<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH.'models/WaterLevelSources/WaterLevelSourceFactory.php';

class Update extends CI_Controller {
    public function index() {
        $water_level = WaterLevelSources\WaterLevelSourceFactory::create_mlitt("");
        echo "<pre>";
        var_dump($water_level->get());
        echo "</pre>";
    }
}