<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH.'models/Entities/MeasuredSourceTypes.php';

class View extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
    }

    public function index($keyword = 'default', $transition = null)
    {
        if ($transition === null) {
            if (is_numeric($keyword)) {
                $transition = $keyword;
                $keyword = 'default';
            } else {
                $transition = 4;
            }
        }
        $this->load->helper(array('security', 'measured_value'));
        $this->load->model('view_model');
        $data = $this->view_model->get_list($keyword, $transition);
        $views = $this->view_model->get_views($keyword);
        $data['views'] = $views;
        $this->load->view('view_list', $data);
    }

    public function values($source_id)
    {
        $this->load->helper(array('security', 'measured_value'));
 
        if (is_numeric($source_id)) {
            $this->load->model('view_model');
            $data = $this->view_model->get_measure_source_data($source_id - 0);
            $back_url = array_reduce(
                array(
                    $this->input->get('keyword'),
                    $this->input->get('transition'),
                ), function ($s, $param) {
                    return isset($param)
                        ? $s . "/{$param}"
                        : $s;
                }, '');
            $data['back_url'] = $back_url;
            $this->load->view(
                'view_measure_source',
                $data);
            return;
        }

        show_404();
    }
}
