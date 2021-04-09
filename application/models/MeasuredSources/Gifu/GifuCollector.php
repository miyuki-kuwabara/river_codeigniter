<?php
namespace MeasuredSources\Gifu {
    defined('BASEPATH') or exit('No direct script access allowed');
    require_once APPPATH.'models/Entities/MeasuredValueFlags.php';
    require_once APPPATH.'models/Entities/MeasuredValueTypes.php';
    require_once APPPATH.'models/HttpGetter.php';
    require_once APPPATH.'models/HttpHeaderParser.php';
    require_once APPPATH.'models/HttpEntitiySpaceReplacer.php';
    require_once APPPATH.'models/MeasuredSources/MeasuredDateNormalizer.php';
    require_once APPPATH.'models/MeasuredSources/IMeasuredSourceCollector.php';

    class GifuCollector implements \MeasuredSources\IMeasuredSourceCollector
    {
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
            $date = date('Y-m-d H:i:s');

            libxml_use_internal_errors(true);
            
            $document = new \DOMDocument();
            $load = $document->loadHTML($response);
            if ($load === false) {
                return null;
            }

            $pres = $document->getElementsByTagName('pre');
            for ($i = $pres->length - 1; 0 <= $i; $i--) {
                $content = $pres->item($i)->textContent;
                $result = $this->extract(
                    $this->entity_space_replacer->replace($content),
                    $date);
                if (!empty($result)) {
                    return $result;
                }
            }
            return array();
        }

        private function extract($content, $acquired_at)
        {
            $datum = array();
            foreach (explode("\n", $content) as $line) {
                if (preg_match('/(\d{1,2}:\d{2})\s+(\d+(?:\.\d+)?)/', $content, $matches)) {
                    $measured_at = $this->measured_date_normalizer->normalize_time($matches[1]);
                    if ($measured_at === null) {
                        continue;
                    }
                    $value = is_numeric($matches[2]) ? $matches[2] - 0 : null;
                    $datum[] = array(
                        'measured_at' => $measured_at,
                        'value_type' => \Entities\MeasuredValueTypes::WATER_LEVEL,
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
