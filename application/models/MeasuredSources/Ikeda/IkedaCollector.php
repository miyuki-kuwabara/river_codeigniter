<?php
namespace MeasuredSources\Ikeda {
    defined('BASEPATH') or exit('No direct script access allowed');
    
    require_once APPPATH.'models/Entities/MeasuredValueFlags.php';
    require_once APPPATH.'models/Entities/MeasuredValueTypes.php';
    require_once APPPATH.'models/HttpGetter.php';
    require_once APPPATH.'models/HttpHeaderParser.php';
    require_once APPPATH.'models/HttpEntitiySpaceReplacer.php';
    require_once APPPATH.'models/MeasuredSources/MeasuredDateNormalizer.php';
    require_once APPPATH.'models/MeasuredSources/IMeasuredSourceCollector.php';

    class IkedaCollector implements \MeasuredSources\IMeasuredSourceCollector
    {
        private $source_url = null;
        private $measured_date_normalizer = null;

        public function __construct($source_url)
        {
            $this->source_url = $source_url;
            $this->measured_date_normalizer = new \MeasuredSources\MeasuredDateNormalizer();
        }

        public function get()
        {
            $getter = new \HttpGetter();
            $data = $this->get_level_data($getter);
            return $data;
        }

        private function get_level_data(\HttpGetter $getter)
        {
            $response = $getter->get($this->source_url);
            $date = new \DateTime();
            libxml_use_internal_errors(true);
            
            $document = new \DOMDocument();

            // おそらくhttp-equivがダブルクォーテーションで括られていないせいで、文字コードをDOMパーサが解析できていない。
            $content = str_replace(
                '<META content="text/html; charset=shift_jis" http-equiv=Content-Type>', 
                '<meta http-equiv="Content-Type" content="text/html; charset=shift_jis">', 
                $response);
            $load = $document->loadHTML($content);
            if ($load === false) {
                return null;
            }

            $divs = $document->getElementsByTagName('div');
            $last_measured_at = null;
            $datum = array();
            foreach ($divs as $div) {
                $text = $div->textContent;
                if ($last_measured_at === null) {
                    if (preg_match('/\d{4}\/\d{2}\/\d{2}(\s+)\d{2}:\d{2}/', $text, $matches)) {
                        $last_measured_at = \DateTime::createFromFormat("Y/m/d{$matches[1]}G:i", $matches[0], new \DateTimeZone('Asia/Tokyo'));
                        if ($last_measured_at === false) {
                            $last_measured_at = null;
                        }
                    }
                } else {
                    if (preg_match('/(\d{1,2}:\d{2})(?:\s|　)+(-?\d+(?:\.\d+)?)m/', $text, $matches)) {
                        $measured_at = $this->measured_date_normalizer->normalize_time_backword($matches[1], $last_measured_at);
                        if ($measured_at === null) {
                            continue;
                        }
                        $value = is_numeric($matches[2]) ? $matches[2] - 0 : null;
                        $datum[] = array(
                            'measured_at' => $measured_at,
                            'value_type' => \Entities\MeasuredValueTypes::WATER_LEVEL,
                            'value' => $value,
                            'flags' => isset($value)
                                ? \Entities\MeasuredValueFlags::NONE
                                : \Entities\MeasuredValueFlags::MISSED,
                            'acquired_at' => $date,
                        );
                        $last_measured_at = $measured_at;
                    }
                }
            }
            return $datum;
        }
    }
}
