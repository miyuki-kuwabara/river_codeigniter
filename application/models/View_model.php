<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH.'models/Entities/MeasuredSourceTypes.php';
require_once APPPATH.'models/Entities/MeasuredValueFlags.php';

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
            ->where('measured_data.measure_source_id = older.measure_source_id')
            ->where('measured_data.value_type = older.value_type')
            ->where('measured_data.measured_at < older.measured_at')
            ->where('older.measured_at <= time_points.measured_at')
            ->get_compiled_select();

        $query = $this->db
            ->select('time_points.measured_at')
            ->select('values_views.measure_value_id AS measure_value_id')
            ->select('values.name, values.link_uri')
            ->select('measured_data.value, measured_data.flags')
            ->from('river_views views')
            ->join("(${time_points}) time_points", "views.keyword = {$this->db->escape($keyword)}", "inner", false)
            ->join('river_measure_values_views values_views', 'views.id = values_views.view_id', 'inner')
            ->join('river_measure_values values', 'values_views.measure_value_id = values.id', 'inner')
            ->join('river_measured_data measured_data',
                "values.measure_source_id = measured_data.measure_source_id AND
                values.value_type = measured_data.value_type AND
                time_points.measured_at >= measured_data.measured_at AND
                NOT EXISTS($older)", 'left')
 //           ->where('views.keyword', $keyword)
            ->order_by('values_views.sort_order, time_points.measured_at')
            ->get();

        $list = array();
        $value_id = null;
        $value = null;
        foreach ($query->result_array() as $row) {
            $disable_value = $row['flags'] == \Entities\MeasuredValueFlags::MISSED
                || $row['flags'] == \Entities\MeasuredValueFlags::CLOSED
                || $row['flags'] == \Entities\MeasuredValueFlags::NOT_YET;
            if ($row['measure_value_id'] !== $value_id) {
                if (!empty($value)) {
                    $list[] = $value;
                }
                $value_id = $row['measure_value_id'];
                $value = array(
                    'name' => $row['name'],
                    'link_uri' => $row['link_uri'],
                    'values' => array()
                );

                // 最初のデータは変化取得用
                $prev = $disable_value ? null : $row['value'];
                continue;
            }
           
            $value['values'][$row['measured_at']] = array(
                'value' => $disable_value
                    ? null
                    : $row['value'],
                'flags' => $row['flags'],
                'difference' => $prev === null || $row['value'] === null || $disable_value
                    ? 0
                    : $row['value'] - $prev
            );
            $prev = $disable_value ? null : $row['value'];
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
}
