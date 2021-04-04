<?php
namespace MeasuredSources\Wakayama {
    defined('BASEPATH') OR exit('No direct script access allowed');
    require_once APPPATH.'models/Entities/MeasuredValueFlags.php';
    require_once APPPATH.'models/HttpGetter.php';
    require_once APPPATH.'models/HttpHeaderParser.php';
    require_once APPPATH.'models/MeasuredSources/Wakayama/LevelValueTypeProvider.php';
    require_once APPPATH.'models/MeasuredSources/Wakayama/DamInflowValueTypeProvider.php';
    require_once APPPATH.'models/MeasuredSources/Wakayama/DamOutflowValueTypeProvider.php';
    require_once APPPATH.'models/MeasuredSources/IMeasuredSourceCollector.php';

    class WakayamaCollector implements \MeasuredSources\IMeasuredSourceCollector {
        private $source_url = null;
        private $value_type_provider = null;
        

        public static function create_level($db, $source_url) {
            return new WakayamaCollector($db, new LevelValueTypeProvider(), $source_url);
        }

        public static function create_dam_inflow($db, $source_url) {
            return new WakayamaCollector($db, new DamInflowValueTypeProvider(), $source_url);
        }

        public static function create_dam_outflow($db, $source_url) {
            return new WakayamaCollector($db, new DamInflowValueTypeProvider(), $source_url);
        }

        private function __construct($db, IValueTypeProvider $value_type_provider, $source_url) {
            $this->value_type_provider = $value_type_provider;
            $this->source_url = $source_url;
        }

        public function get() {
            $getter = new \HttpGetter();
            $data = $this->get_level_data($getter);
            return $data;
        }

        private function get_level_data(\HttpGetter $getter) {
            $response = $getter->get($this->source_url);
            $date = date('Y-m-d H:i:s');

            libxml_use_internal_errors(true);
            
            $document = new \DOMDocument();
            $load = $document->loadHTML($response);
            if ($load === false) return null;

            $pres = $document->getElementsByTagName('pre');
            foreach ($pres as $pre) {
                $result = $this->extract($pre->textContent, $date);
                if (!empty($result)) return $result;
            }
            return array();
        }

        private function extract($content, $acquired_at) {
            $now = localtime(time(), true);
            $datum = array();
            $value_type = $this->value_type_provider->get();
            foreach (explode("\n", $content) as $line) {
                if (preg_match('/(\d{1,2}):(\d{2})\s+(\S+)/', $content, $matches)) {
                    $hour = intval($matches[1]);
                    $minute = intval($matches[2]);
                    $value = is_numeric($matches[3] ? $matches[3] - 0 : null);
                    if ($now['tm_hour'] < $hour || ($now['tm_hour'] === $hour && $now['tm_min'] < $minute)) {
                        $timestamp = mktime($hour, $minute, 0, $now['tm_mon'] + 1, $now['tm_mday'] - 1, $now['tm_year'] + 1900);
                    } else {
                        $timestamp = mktime($hour, $minute, 0, $now['tm_mon'] + 1, $now['tm_mday'], $now['tm_year'] + 1900);
                    }
                    $datum[] = array(
                        'measured_at' => date('Y-m-d H:i', $timestamp),
                        'value_type' => $value_type,
                        'value' => $value,
                        'flags' => isset($value) ? \Entities\MeasuredValueFlags::NONE : \Entities\MeasuredValueFlags::MISSED,
                        'acquired_at' => $acquired_at,
                    );
                }
            }
            return $datum;
        }
    }    
}

