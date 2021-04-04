<?php
namespace MeasuredSources\Araizeki {
    defined('BASEPATH') OR exit('No direct script access allowed');
    require_once APPPATH.'models/Entities/MeasuredValueTypes.php';
    require_once APPPATH.'models/Entities/MeasuredValueFlags.php';
    require_once APPPATH.'models/HttpGetter.php';
    require_once APPPATH.'models/HttpHeaderParser.php';
    require_once APPPATH.'models/HttpEntitiySpaceReplacer.php';
    require_once APPPATH.'models/MeasuredSources/IMeasuredSourceCollector.php';

    class AraizekiCollector implements \MeasuredSources\IMeasuredSourceCollector {
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
                $replacer = new \HttpEntitiySpaceReplacer();
                
                foreach ($list_items as $list_item) {
                    $text = mb_convert_kana(
                        $replacer->replace($list_item->textContent), 'as');
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

