<?php
namespace MeasuredSources\Nara {
    defined('BASEPATH') or exit('No direct script access allowed');
    require_once APPPATH.'models/Entities/MeasuredValueFlags.php';
    require_once APPPATH.'models/Entities/MeasuredValueTypes.php';
    require_once APPPATH.'models/HttpGetter.php';
    require_once APPPATH.'models/HttpHeaderParser.php';
    require_once APPPATH.'models/HttpEntitiySpaceReplacer.php';
    require_once APPPATH.'models/MeasuredSources/MeasuredDateNormalizer.php';
    require_once APPPATH.'models/MeasuredSources/IMeasuredSourceCollector.php';

    class NaraCollector implements \MeasuredSources\IMeasuredSourceCollector
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
            $date = new \DateTime();

            libxml_use_internal_errors(true);
            
            $document = new \DOMDocument();
            $load = $document->loadHTML($response);
            if ($load === false) {
                return null;
            }

            $tables = $document->getElementsByTagName('table');
            foreach ($tables as $table) {
                $class = $table->getAttribute('class');
                if (preg_match('/\bdatatable\b/', $class)) {
                    $result = $this->extract($table, $date);
                    if (!empty($result)) {
                        return $result;
                    }
                }
            }
            return array();
        }

        private function extract($table, $acquired_at)
        {
            $datum = array();

            $rows = $table->getElementsByTagName('tr');
            foreach ($rows as $row) {
                $cells = $row->getElementsByTagName('td');
                if ($cells->length < 2) {
                    continue;
                }
                
                $measured_at = $this->extract_measured_at_cell($cells->item(0));
                if (is_null($measured_at)) {
                    continue;
                }

                $value = $this->extract_value_cell($cells->item(1));
                $datum[] = array(
                    'measured_at' => $measured_at,
                    'value_type' => \Entities\MeasuredValueTypes::WATER_LEVEL,
                    'value' => $value,
                    'flags' => isset($value) ? \Entities\MeasuredValueFlags::NONE : \Entities\MeasuredValueFlags::MISSED,
                    'acquired_at' => $acquired_at,
                );
            }
            return $datum;
        }

        private function extract_measured_at_cell($cell)
        {
            $text = $this->entity_space_replacer->replace($cell->textContent);
            return $this->measured_date_normalizer->normalize_datetime($text);
        }

        private function extract_value_cell($cell)
        {
            foreach ($cell->childNodes as $node) {
                $text = trim($this->entity_space_replacer->replace($node->textContent));
                if (!is_numeric($text)) {
                    continue;
                }
                return $text - 0;
            }
            return null;
        }
    }
}
