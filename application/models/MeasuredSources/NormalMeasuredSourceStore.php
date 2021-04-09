<?php
namespace MeasuredSources {
    defined('BASEPATH') or exit('No direct script access allowed');
    require_once APPPATH.'models/MeasuredSources/IMeasuredSourceStore.php';
    
    class NormalMeasuredSourceStore implements IMeasuredSourceStore
    {
        private $db = null;
        private $id = null;
        
        public function __construct($db, $id)
        {
            $this->db = $db;
            $this->id = $id;
        }

        public function store($datum)
        {
        }
    }
}
