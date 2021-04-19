<?php
namespace MeasuredSources\Kyoto {
    defined('BASEPATH') or exit('No direct script access allowed');
    
    require_once APPPATH.'models/Entities/MeasuredValueFlags.php';
    require_once APPPATH.'models/Entities/MeasuredValueTypes.php';
    require_once APPPATH.'models/HttpGetter.php';
    require_once APPPATH.'models/HttpHeaderParser.php';
    require_once APPPATH.'models/HttpEntitiySpaceReplacer.php';
    require_once APPPATH.'models/MeasuredSources/MeasuredDateNormalizer.php';
    require_once APPPATH.'models/MeasuredSources/IMeasuredSourceCollector.php';

    class KyotoCollector implements \MeasuredSources\IMeasuredSourceCollector
    {
        const MARKER_EXTRACT_START = '12時間履歴';
        const MARKER_EXTRACT_END = '1時間履歴';

        private $source_url = null;
        private $entity_space_replacer = null;
        private $measured_date_normalizer = null;

        public function __construct($source_url)
        {
            $this->source_url = $source_url;
            $this->entity_space_replacer = new \HttpEntitiySpaceReplacer();
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
            $load = $document->loadHTML($response);
            if ($load === false) {
                return null;
            }

            $hrs = $document->getElementsByTagName('hr');
            if ($hrs->length < 5) {
                return null;
            }
            $element = $hrs->item(4);
            $buff = '';
            $datum = array();
            while (null != ($element = $element->nextSibling)) {
                $buff .= $this->entity_space_replacer->replace($element->textContent);
                if (preg_match('/\b(\d{1,2}:\d{2})\b\s+(\d+(?:\.\d+)?)m/', $buff, $matches)) {
                    $measured_at = $this->measured_date_normalizer->normalize_time($matches[1]);
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
                    $buff = '';
                }
            }
            return $datum;
        }
    }
}
