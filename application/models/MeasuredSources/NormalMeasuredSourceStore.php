<?php
namespace MeasuredSources {
    defined('BASEPATH') or exit('No direct script access allowed');
    require_once APPPATH.'models/MeasuredSources/IMeasuredSourceStore.php';
    
    class NormalMeasuredSourceStore implements IMeasuredSourceStore
    {
        private $db = null;
        private $id = null;
        private $date_from = null;

        public function __construct($db, $id)
        {
            $this->db = $db;
            $this->id = $id;
            list($y, $m, $d, $h) = explode('/', date('Y/m/d/H'));
            $this->date_from = mktime(intval($h), 0, 0, intval($m), intval($d) - 1, intval($y));
        }

        public function store($datum)
        {
            if (empty($datum)) {
                return;
            }

            $this->db->trans_start();
            
            $this->delete_old_data();
            $this->update_or_insert(
                $this->filter_datum($datum));

            $this->db->trans_complete();
        }

        private function filter_datum($datum)
        {
            $date_from = $this->date_from;
            return array_filter($datum, function ($data) use ($date_from) {
                return $date_from <= $data['measured_at']->format('U')
                    && $data['measured_at']->format('i:s') === '00:00';
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
                    ->set('acquired_at', $data['acquired_at']->format('Y-m-d H:i:s'))
                    ->where('measure_source_id', $this->id)
                    ->where('measured_at', $data['measured_at']->format('Y-m-d H:i'))
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
                    'measured_at' => $data['measured_at']->format('Y-m-d H:i'),
                    'value_type' => $data['value_type'],
                    'value' => $data['value'],
                    'flags' => $data['flags'],
                    'acquired_at' => $data['acquired_at']->format('Y-m-d H:i:s'),
                );
            }, $remain);

            $this->db
                ->set_insert_batch($rows)
                ->insert_batch('river_measured_data');
        }

        private function delete_old_data()
        {
            $this->db
                ->where('measure_source_id', $this->id)
                ->where('measured_at <', date('Y-m-d H:i', $this->date_from))
                ->delete('river_measured_data');
        }
    }
}
