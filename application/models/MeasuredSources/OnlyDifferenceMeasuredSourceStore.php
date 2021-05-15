<?php
namespace MeasuredSources {
    defined('BASEPATH') or exit('No direct script access allowed');
    require_once APPPATH.'models/MeasuredSources/IMeasuredSourceStore.php';
    
    class OnlyDifferenceMeasuredSourceStore implements IMeasuredSourceStore
    {
        private $db = null;
        private $id = null;
        private $date_from = null;

        public function __construct($db, $id)
        {
            $this->db = $db;
            $this->id = $id;
            $this->date_from = strtotime('-1 day');
        }

        public function store($datum)
        {
            if (empty($datum)) {
                return;
            }

            $this->db->trans_start();
            
            $this->insert($datum);
            $this->delete_old_data();

            $this->db->trans_complete();
        }

        private function get_latest_data()
        {
            $sub_query = $this->db
                ->from('river_measured_data AS newer')
                ->where('river_measured_data.measure_source_id = newer.measure_source_id')
                ->where('river_measured_data.value_type = newer.value_type')
                ->where('river_measured_data.measured_at < newer.measured_at')
                ->get_compiled_select();
            $query = $this->db
                ->select('value_type, measured_at, value, flags')
                ->from('river_measured_data')
                ->where('measure_source_id', $this->id)
                ->where("NOT EXISTS(${sub_query})")
                ->get();

            return array_reduce(
                $query->result(),
                function ($array, $row) {
                    $array[$row->value_type] = $row;
                    return $array;
                }, array());
        }


        private function insert($datum)
        {
            $latest = $this->get_latest_data();
            $rows = array();
            foreach ($datum as $data) {
                if (array_key_exists($data['value_type'], $latest)) {
                    $latest_data = $latest[$data['value_type']];
                    if ($latest_data->value == $data['value']
                        && $latest_data->flags == $data['flags']) {
                        continue;
                    }
                }
                $rows[] = array(
                    'measure_source_id' => $this->id,
                    'measured_at' => $data['measured_at']->format('Y-m-d H:i:s'),
                    'value_type' => $data['value_type'],
                    'value' => $data['value'],
                    'flags' => $data['flags'],
                    'acquired_at' => $data['acquired_at']->format('Y-m-d H:i:s'),
                );
            }
            if (empty($rows)) {
                return;
            }

            $this->db
                ->set_insert_batch($rows)
                ->insert_batch('river_measured_data');
        }

        private function delete_old_data()
        {
            // 期限切れよりさらに1レコード古いものを残す。
            // MySQLでは変更対象の表をサブクエリで直接参照できないので、一旦SELECTしている。
            $expired = $this->db
                ->select('measure_source_id, value_type, measured_at')
                ->from('river_measured_data')
                ->where('measure_source_id', $this->id)
                ->where('measured_at <', date('Y-m-d H:i:s', $this->date_from))
                ->get_compiled_select();
            $sub_query = $this->db
                ->select('1', false)
                ->from("(${expired}) expired", false)
                ->where('river_measured_data.measure_source_id = expired.measure_source_id')
                ->where('river_measured_data.value_type = expired.value_type')
                ->where('river_measured_data.measured_at < expired.measured_at')
                ->get_compiled_select();
            $this->db
                ->where('river_measured_data.measure_source_id', $this->id)
                ->where("EXISTS(${sub_query})")
                ->delete('river_measured_data');
        }
    }
}
