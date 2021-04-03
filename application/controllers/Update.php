<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH.'models/MeasuredSources/MeasuredSourceFactory.php';

class Update extends CI_Controller {
    public function index() {
        $water_level = MeasuredSources\MeasuredSourceFactory::create_wakayama_dam_inflow("");
        $water_level = MeasuredSources\MeasuredSourceFactory::create_wakayama_dam_outflow("");
        $water_level = MeasuredSources\MeasuredSourceFactory::create_wakayama_level("");
        $water_level = MeasuredSources\MeasuredSourceFactory::create_mlitt("");
        $water_level = MeasuredSources\MeasuredSourceFactory::create_mlitt_dam("");
        $water_level = MeasuredSources\MeasuredSourceFactory::create_araizeki("");
        echo "<pre>";
        var_dump($water_level->get());
        echo "</pre>";
    }

    public function db() {
        $query = $this->db->get('river_measure_sources');
        var_dump($query->result());
    }
}