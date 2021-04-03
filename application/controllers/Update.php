<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH.'models/WaterLevelSource.php';

class Update extends CI_Controller {
    public function index() {
        $water_level = WaterLevelSource::create_mlitt("");
        echo "<pre>";
        var_dump($water_level->get());
        echo "</pre>";
    }

    public function db() {
        $query = $this->db->get('river_measure_sources');
        var_dump($query->result());
    }
}