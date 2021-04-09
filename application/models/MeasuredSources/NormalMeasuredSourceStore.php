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
            if (empty($datum)) {
                return;
            }

            $this->db->trans_start();
            
            $this->update_or_insert(
                $this->filter_datum($datum));

            $this->db->trans_complete();
        }

        private function filter_datum($datum)
        {
            return array_filter($datum, function ($data) {
                return preg_match('/\s+\d{2}:00$/', $data['measured_at']) == 1;
            });
        }

        private function update_or_insert($datum)
        {
            $id = $this->id;
            $remain = array();
            foreach ($datum as $data) {
                $this->db
                    ->set('value', $data['value'])
                    ->set('flags', $data['flags'])
                    ->set('acquired_at', $data['acquired_at'])
                    ->where('measure_source_id', $this->id)
                    ->where('measured_at', $data['measured_at'])
                    ->where('value_type', $data['value_type'])
                    ->update('river_measured_data');
                if ($this->db->affected_rows() == 0) {
                    $remain[] = $data;
                }
            }

            if (empty($remain)) {
                return;
            }

            $rows = array_map(function ($data) use ($id) {
                return array(
                    'measure_source_id' => $id,
                    'measured_at' => $data['measured_at'],
                    'value_type' => $data['value_type'],
                    'value' => $data['value'],
                    'flags' => $data['flags'],
                    'acquired_at' => $data['acquired_at'],
                );
            }, $remain);

            $this->db
                ->set_insert_batch($rows)
                ->insert_batch('river_measured_data');
        }
    }
}
