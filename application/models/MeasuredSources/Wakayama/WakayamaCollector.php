<?php
namespace MeasuredSources\Wakayama {
    defined('BASEPATH') or exit('No direct script access allowed');
    require_once APPPATH.'models/Entities/MeasuredValueFlags.php';
    require_once APPPATH.'models/HttpGetter.php';
    require_once APPPATH.'models/HttpHeaderParser.php';
    require_once APPPATH.'models/MeasuredSources/MeasuredDateNormalizer.php';
    require_once APPPATH.'models/MeasuredSources/Wakayama/LevelValueTypeProvider.php';
    require_once APPPATH.'models/MeasuredSources/Wakayama/DamInflowValueTypeProvider.php';
    require_once APPPATH.'models/MeasuredSources/Wakayama/DamOutflowValueTypeProvider.php';
    require_once APPPATH.'models/MeasuredSources/IMeasuredSourceCollector.php';

    class WakayamaCollector implements \MeasuredSources\IMeasuredSourceCollector
    {
        private $source_url = null;
        private $value_type_provider = null;
        

        public static function create_level($source_url)
        {
            return new WakayamaCollector(new LevelValueTypeProvider(), $source_url);
        }

        public static function create_dam_inflow($source_url)
        {
            return new WakayamaCollector(new DamInflowValueTypeProvider(), $source_url);
        }

        public static function create_dam_outflow($source_url)
        {
            return new WakayamaCollector(new DamInflowValueTypeProvider(), $source_url);
        }

        private function __construct(IValueTypeProvider $value_type_provider, $source_url)
        {
            $this->value_type_provider = $value_type_provider;
            $this->source_url = $source_url;
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

            $pres = $document->getElementsByTagName('pre');
            foreach ($pres as $pre) {
                $result = $this->extract($pre->textContent, $date);
                if (!empty($result)) {
                    return $result;
                }
            }
            return array();
        }

        private function extract($content, $acquired_at)
        {
            $normalizer = new \MeasuredSources\MeasuredDateNormalizer();
            $datum = array();
            $value_type = $this->value_type_provider->get();
            foreach (explode("\n", $content) as $line) {
                if (preg_match('/(\d{1,2}:\d{2})\s+(\S+)/', $line, $matches)) {
                    $measured_at = $normalizer->normalize_time($matches[1]);
                    if ($measured_at === null) {
                        continue;
                    }
                    $value = is_numeric($matches[2]) ? $matches[2] - 0 : null;
                    $datum[] = array(
                        'measured_at' => $measured_at,
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
