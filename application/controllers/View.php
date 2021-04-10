<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH.'models/MeasuredSources/MeasuredSourceFactory.php';

class View extends CI_Controller
{
    public function index($keyword = 'default', $transition = null)
    {
        if ($transition === null) {
            if (is_numeric($keyword)) {
                $transition = $keyword;
                $keyword = 'default';
            } else {
                $transition = 15;
            }
        }

        $this->load->model('view_model');
        $this->load->view('view_list', $this->view_model->get_list($keyword, $transition));
        $this->output->enable_profiler();
    }
}
