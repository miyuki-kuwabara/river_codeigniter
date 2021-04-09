<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH.'models/MeasuredSources/MeasuredSourceFactory.php';

class Update extends CI_Controller
{
    public function index()
    {
        echo "<pre>";
        $this->load->model('measured_sources_model');
        $this->measured_sources_model->update();
        echo "</pre>";
    }
}
