<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH.'models/MeasuredSources/MeasuredSourceFactory.php';

class Measured_sources_model extends CI_Model
{
    public function update()
    {
        ini_set('max_execution_time', 600);
        $sources = $this->get_update_sources();
        array_walk($sources, function ($source) {
            $source->update();
        });
    }

    private function get_update_sources()
    {
        $latest = date('Y-m-d H') . ':00';
        $sub_query = $this->db
            ->select('id')
            ->from('river_measured_data')
            ->where('river_measure_sources.id = measure_source_id')
            ->where('measured_at >=', $latest)
            ->where('value !=', null)
            ->get_compiled_select();
        $query = $this->db
            ->select('id, type, uri')
            ->from('river_measure_sources')
            ->where("NOT EXISTS($sub_query)")
            ->get();
        
        $db = $this->db;
        return array_map(function ($row) use (&$db) {
            return MeasuredSources\MeasuredSourceFactory::create($db, $row->id, $row->type, $row->uri);
        }, $query->result());
    }
}
