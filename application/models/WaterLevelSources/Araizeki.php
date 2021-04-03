<?php
namespace WaterLevelSources {
    defined('BASEPATH') OR exit('No direct script access allowed');
    require_once APPPATH.'models/Entities/MeasuredValueTypes.php';
    require_once APPPATH.'models/Entities/MeasuredValueFlags.php';
    require_once APPPATH.'models/HttpGetter.php';
    require_once APPPATH.'models/HttpHeaderParser.php';
    require_once APPPATH.'models/WaterLevelSources/IWaterLevelSource.php';

    class Araizeki implements IWaterLevelSource {
        const ANCESTOR_DIV_ID = "idx-saigai";
        private $source_url = null;
        
        public function __construct($db, $source_url) {
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

            $div = $document->getElementById(self::ANCESTOR_DIV_ID);
            if ($div !== null) {
                $list_items = $div->getElementsByTagName("li");
                $replace = array('&nbsp;', '&emsp;', '&ensp;');
                $replace = array_merge(
                    $replace,
                    array_map(function($s) {
                        return html_entity_decode($s, ENT_COMPAT, 'UTF-8');
                    }, $replace));
                
                foreach ($list_items as $list_item) {
                    $text = str_replace($replace, ' ', $list_item->textContent);
                    $text = mb_convert_kana($text, 'as');
                    if (preg_match("/現在の洗堰放流量\s*(\d+(?:\.\d+)?)m³\/s/", $text, $matches)) {
                        return array(
                            array(
                                'measured_at' => $date,
                                'value_type' => \Entities\MeasuredValueTypes::OUTFLOW,
                                'value' => $matches[1] - 0,
                                'flags' => \Entities\MeasuredValueFlags::NONE,
                                'acquired_at' => $date, 
                            )
                        );
                    }
                }
                return array(
                    'measured_at' => $date,
                    'value_type' => \Entities\MeasuredValueTypes::OUTFLOW,
                    'value' => null,
                    'flags' => \Entities\MeasuredValueFlags::MISSED,
                    'acquired_at' => $date, 
                );
            }
            return array();
        }
    }    
}

