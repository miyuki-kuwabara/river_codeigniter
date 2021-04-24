<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH.'models/Entities/MeasuredSourceTypes.php';
require_once APPPATH.'models/Entities/MeasuredValueFlags.php';
require_once APPPATH.'models/Entities/MeasuredValueTypes.php';

class View_model extends CI_Model
{
    private $week = null;
    public function __construct()
    {
        $this->week = array('日', '月', '火', '水', '木', '金', '土');
    }

    public function get_list($keyword, $transition)
    {
        if ($transition == 0) {
            $transition = 3;
        }

        $timestamps = array_map(
            function ($relative) {
                return strtotime("${relative} hour");
            },
        range(-$transition, 0));
        $time_points = join(' UNION ', array_map(
            function ($timestamp) {
                $time = date('Y-m-d H', $timestamp);
                return "SELECT '${time}:00' AS measured_at";
            },
        $timestamps));

        $older = $this->db
            ->select('1', false)
            ->from('river_measured_data older')
            ->where('last_data.measure_source_id = older.measure_source_id')
            ->where('last_data.value_type = older.value_type')
            ->where('last_data.measured_at < older.measured_at')
            ->where('older.measured_at <= time_points.measured_at')
            ->get_compiled_select();

        $query = $this->db
            ->select('time_points.measured_at')
            ->select('values_views.measure_value_id')
            ->select('values.measure_source_id, values.name, values.link_uri')
            ->select("IF(sources.type = {$this->db->escape(\Entities\MeasuredSourceTypes::ARAIZEKI)}, last_data.value, measured_data.value) AS value")
            ->select("IF(sources.type = {$this->db->escape(\Entities\MeasuredSourceTypes::ARAIZEKI)}, last_data.flags, measured_data.flags) AS flags")
            ->from('river_views views')
            ->join('river_measure_values_views values_views', 'views.id = values_views.view_id', 'inner')
            ->join('river_measure_values values', 'values_views.measure_value_id = values.id', 'inner')
            ->join('river_measure_sources sources', 'values.measure_source_id = sources.id', 'inner')
            ->join("(${time_points}) time_points", "views.keyword = {$this->db->escape($keyword)}", "inner", false)
            ->join('river_measured_data measured_data',
                'values.measure_source_id = measured_data.measure_source_id AND ' .
                'values.value_type = measured_data.value_type AND ' .
                'time_points.measured_at = measured_data.measured_at', 'left')
            ->join('river_measured_data last_data',
                'values.measure_source_id = last_data.measure_source_id AND ' .
                'values.value_type = last_data.value_type AND ' .
                'time_points.measured_at >= last_data.measured_at AND ' .
                "NOT EXISTS($older)", 'left')
 //           ->where('views.keyword', $keyword)
            ->order_by('values_views.sort_order, time_points.measured_at')
            ->get();

        $list = array();
        $value_id = null;
        $value = null;
        foreach ($query->result_array() as $row) {
            if ($row['measure_value_id'] !== $value_id) {
                if (!empty($value)) {
                    $list[] = $value;
                }
                $value_id = $row['measure_value_id'];
                $value = array(
                    'name' => $row['name'],
                    'link_uri' => $row['link_uri'],
                    'measure_source_id' => $row['measure_source_id'],
                    'values' => array()
                );
            }
           
            $value['values'][$row['measured_at']] = array(
                'value' => $row['value'],
                'flags' => $row['flags'],
            );
        }
        if (!empty($value)) {
            $list[] = $value;
        }

        array_shift($timestamps);
        $firstday = $this->week[date('w', $timestamps[0])];
        return array(
            'list' => $list,
            'first_date' => date('m/d', $timestamps[0]) . "(${firstday})",
            'times' => array_map(function ($timestamp) {
                return date('H', $timestamp) . ':00';
            }, $timestamps));
    }

    public function get_measure_source_data($measure_source_id)
    {
        $measure_source = $this->get_measure_source_with_values($measure_source_id);
        $measured_data = $this->get_measured_data($measure_source_id);
        $measure_source['measured_data'] = $measured_data;
        return $measure_source;
    }

    private function get_measure_source_with_values($measure_source_id)
    {
        $query = $this->db
            ->select('sources.id AS measure_source_id, sources.name AS measure_source_name, sources.type AS measure_source_type')
            ->select('water_level.name AS water_level_name, water_level.unit AS water_level_unit')
            ->select('inflow.name AS inflow_name, inflow.unit AS inflow_unit')
            ->select('outflow.name AS outflow_name, outflow.unit AS outflow_unit')
            ->select('percentage.name AS percentage_name, percentage.unit AS percentage_unit')
            ->select('amount.name AS amount_name, amount.unit AS amount_unit')
            ->from('river_measure_sources sources')
            ->join(
                'river_measure_values water_level',
                'sources.id = water_level.measure_source_id AND '.
                "water_level.value_type = {$this->db->escape(\Entities\MeasuredValueTypes::WATER_LEVEL)}",
                'left')
            ->join(
                'river_measure_values inflow',
                'sources.id = inflow.measure_source_id AND '.
                "inflow.value_type = {$this->db->escape(\Entities\MeasuredValueTypes::INFLOW)}",
                'left')
            ->join(
                'river_measure_values outflow',
                'sources.id = outflow.measure_source_id AND '.
                "outflow.value_type = {$this->db->escape(\Entities\MeasuredValueTypes::OUTFLOW)}",
                'left')
            ->join(
                'river_measure_values percentage',
                'sources.id = percentage.measure_source_id AND '.
                "percentage.value_type = {$this->db->escape(\Entities\MeasuredValueTypes::PERCENTAGE_OF_STORAGE)}",
                'left')
            ->join(
                'river_measure_values amount',
                'sources.id = amount.measure_source_id AND '.
                "amount.value_type = {$this->db->escape(\Entities\MeasuredValueTypes::AMOUNT_OF_STORAGE)}",
                'left')
            ->where('sources.id', $measure_source_id)
            ->get();
        return $query->row_array();
    }

    private function get_measured_data($measure_source_id)
    {
        $gt_type = $this->db
            ->select('1', false)
            ->from('river_measured_data gt_type')
            ->where('time_points.measure_source_id = gt_type.measure_source_id')
            ->where('time_points.measured_at = gt_type.measured_at')
            ->where('time_points.value_type < gt_type.value_type')
            ->get_compiled_select();
        $query = $this->db
            ->select("DATE_FORMAT(time_points.measured_at, '%Y/%m/%d %H:%i') AS measured_at, DATE_FORMAT(time_points.measured_at, '%m/%d') AS measured_date, TIME_FORMAT(time_points.measured_at, '%H:%i') AS measured_time")
            ->select('water_levels.value AS water_level_value, water_levels.flags AS water_level_flags')
            ->select('inflows.value AS inflow_value, inflows.flags AS inflow_flags')
            ->select('outflows.value AS outflow_value, outflows.flags AS outflow_flags')
            ->select('percentages.value AS percentage_value, percentages.flags AS percentage_flags')
            ->select('amounts.value AS amount_value, amounts.flags AS amount_flags')
            ->from('river_measured_data time_points')
            ->join(
                'river_measured_data water_levels',
                'time_points.measure_source_id = water_levels.measure_source_id AND '.
                'time_points.measured_at = water_levels.measured_at AND '.
                "water_levels.value_type = {$this->db->escape(\Entities\MeasuredValueTypes::WATER_LEVEL)}",
                'left')
            ->join(
                'river_measured_data inflows',
                'time_points.measure_source_id = inflows.measure_source_id AND '.
                'time_points.measured_at = inflows.measured_at AND '.
                "inflows.value_type = {$this->db->escape(\Entities\MeasuredValueTypes::INFLOW)}",
                'left')
            ->join(
                'river_measured_data outflows',
                'time_points.measure_source_id = outflows.measure_source_id AND '.
                'time_points.measured_at = outflows.measured_at AND '.
                "outflows.value_type = {$this->db->escape(\Entities\MeasuredValueTypes::OUTFLOW)}",
                'left')
            ->join(
                'river_measured_data percentages',
                'time_points.measure_source_id = percentages.measure_source_id AND '.
                'time_points.measured_at = percentages.measured_at AND '.
                "percentages.value_type = {$this->db->escape(\Entities\MeasuredValueTypes::PERCENTAGE_OF_STORAGE)}",
                'left')
            ->join(
                'river_measured_data amounts',
                'time_points.measure_source_id = amounts.measure_source_id AND '.
                'time_points.measured_at = amounts.measured_at AND '.
                "amounts.value_type = {$this->db->escape(\Entities\MeasuredValueTypes::AMOUNT_OF_STORAGE)}",
                'left')
            ->where('time_points.measure_source_id', $measure_source_id)
            ->where('time_points.measured_at < NOW()')
            ->where("NOT EXISTS(${gt_type})")
            ->order_by('time_points.measured_at DESC')
            ->get();

        return $query->result_array();
    }
}
