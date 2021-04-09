<?php
namespace MeasuredSources {
    defined('BASEPATH') or exit('No direct script access allowed');
    require_once APPPATH.'models/MeasuredSources/IMeasuredSourceCollector.php';
    
    class NullMeasuredSourceCollector implements IMeasuredSourceCollector
    {
        public function get()
        {
            return array();
        }
    }
}
