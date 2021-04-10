<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH.'models/MeasuredSources/MeasuredSourceFactory.php';

class Measured_sources_model extends CI_Model
{
    public function update()
    {
        ini_set('max_execution_time', 600);
        date_default_timezone_set('Asia/Tokyo');
        $sources = $this->get_update_sources();
        array_walk($sources, function ($source) {
            $source->update();
        });
    }

    private function get_update_sources()
    {
        $latest = date('Y-m-d H:i:s', strtotime('-1 hour'));
        $sub_araizeki = $this->db
            ->select('id')
            ->from('river_measured_data')
            ->where('river_measure_sources.id = measure_source_id')
            ->where('measured_at >=', $latest)
            ->get_compiled_select();
        $latest = date('Y-m-d H') . ':00';
        $sub_other = $this->db
            ->select('id')
            ->from('river_measured_data')
            ->where('river_measure_sources.id = measure_source_id')
            ->where('measured_at >=', $latest)
            ->where('value !=', null)
            ->get_compiled_select();
        $query = $this->db
            ->select('id, type, uri')
            ->from('river_measure_sources')
            ->or_group_start()
                ->where('type', \Entities\MeasuredSourceTypes::ARAIZEKI)
                ->where("NOT EXISTS($sub_araizeki)")
            ->group_end()
            ->or_group_start()
                ->where('type !=', \Entities\MeasuredSourceTypes::ARAIZEKI)
                ->where("NOT EXISTS($sub_other)")
            ->group_end()
            ->get();
        
        $db = $this->db;
        return array_map(function ($row) use (&$db) {
            return MeasuredSources\MeasuredSourceFactory::create($db, $row->id, $row->type, $row->uri);
        }, $query->result());
    }
}
