<?php
namespace MeasuredSources\Mie {

use DOMElement;
use DOMExplorer;

defined('BASEPATH') or exit('No direct script access allowed');
    
    require_once APPPATH.'models/Entities/MeasuredValueFlags.php';
    require_once APPPATH.'models/Entities/MeasuredValueTypes.php';
    require_once APPPATH.'models/HttpGetter.php';
    require_once APPPATH.'models/HttpHeaderParser.php';
    require_once APPPATH.'models/HttpEntitiySpaceReplacer.php';
    require_once APPPATH.'models/DOM/DOMElementIterator.php';
    require_once APPPATH.'models/MeasuredSources/MeasuredDateNormalizer.php';
    require_once APPPATH.'models/MeasuredSources/IMeasuredSourceCollector.php';

    class MieCollector implements \MeasuredSources\IMeasuredSourceCollector
    {
        private $source_url = null;
        private $observatory_name = null;
        private $entity_space_replacer = null;
        private $measured_date_normalizer = null;

        public function __construct($source_url, $extra_string)
        {
            $this->source_url = $source_url;
            $this->observatory_name = $extra_string;
            $this->entity_space_replacer = new \HttpEntitiySpaceReplacer();
            $this->measured_date_normalizer = new \MeasuredSources\MeasuredDateNormalizer();
        }

        public function get()
        {
            $getter = new \HttpGetter();
            $data = $this->extruct_page($getter);
            return $data;
        }

        private function find_id_from_table(\DOMElement $table)
        {
            // 防災みえ.jpの水位情報ページはtable要素が入れ子になっているので
            // getElementsByTagNameでtrを列挙するのはNG。
            // 直接の子ノードを辿って目的の要素を探す必要がある。
            $tbodies = \DOM\DOMElementIterator::CreateWithTagSpec($table->childNodes, 'tbody');
            $tbodies->seek(0);
            $tbody = $tbodies->current();
            foreach (\DOM\DOMElementIterator::CreateWithTagSpec($tbody->childNodes, 'tr') as $row) {
                $index = 0;

                // 4つめのth要素に観測所名が入っている。
                foreach (\DOM\DOMElementIterator::Create($row->childNodes) as $cell) {
                    if ($cell->tagName != 'th' && $cell->tagName != 'td') {
                        continue;
                    }
                    $index++;
                    if ($index == 4) {
                        $str = trim($cell->textContent);
                        if ($str == $this->observatory_name) {
                            // その観測所名に、ポップアップするdivへのリンクが貼られている
                            $anchors = $cell->getElementsByTagName('a');
                            if (1 <= $anchors->length) {
                                $anchor = $anchors->item(0);
                                if (preg_match('/#(\S+)/', $anchor->getAttribute('href'), $matches)) {
                                    return $matches[1];
                                }
                            }
                            return null;
                        }
                        break;
                    }
                }
            }
            return null;
        }

        private function find_measured_data_table(\DOMElement $float)
        {
            $tables = $float->getElementsByTagName('table');
            foreach ($tables as $table) {
                $class = $table->getAttribute('class');
                if (preg_match('/\bfloat_table_left\b/', $class)) {
                    return $table;
                }
            }
            return null;
        }

        private function extruct_measured_data_table(\DOMElement $table, \DateTime $acquired_at)
        {
            $current = new \DateTime('-24 hours');
            $datum = array();
            $tbodies = \DOM\DOMElementIterator::CreateWithTagSpec($table->childNodes, 'tbody');
            $tbodies->seek(0);
            foreach (\DOM\DOMElementIterator::CreateWithTagSpec($tbodies->current()->childNodes, 'tr') as $row) {
                $cells = $row->getElementsByTagName('td');
                if ($cells->length < 2) {
                    continue;
                }

                $str = $cells->item(0)->textContent;
                $measured_at = $this->measured_date_normalizer->normalize_datetime($str);
                if ($measured_at === null) {
                    $measured_at = $this->measured_date_normalizer->normalize_time_forward($str, $current);
                    if ($measured_at === null) {
                        continue;
                    }
                }

                $value = null;
                foreach ($cells->item(1)->childNodes as $node) {
                    $str = trim($this->entity_space_replacer->replace($node->textContent));
                    if (!is_numeric($str)) {
                        continue;
                    }
                    $value = $str - 0;
                    break;
                }

                $datum[] = array(
                    'measured_at' => $measured_at,
                    'value_type' => \Entities\MeasuredValueTypes::WATER_LEVEL,
                    'value' => $value,
                    'flags' => isset($value) ? \Entities\MeasuredValueFlags::NONE : \Entities\MeasuredValueFlags::MISSED,
                    'acquired_at' => $acquired_at,
                );

                $current = $measured_at;
            }
            return $datum;
        }

        private function extruct_page(\HttpGetter $getter)
        {
            $response = $getter->get($this->source_url);
            $date = new \DateTime();

            libxml_use_internal_errors(true);
            
            $document = new \DOMDocument();
            $load = $document->loadHTML($response);
            if ($load === false) {
                return null;
            }

            $parent = $document->getElementById('kinkyujioyakudati_right_contents_in');
            if ($parent === null) {
                return null;
            }
            $tables = $parent->getElementsByTagName('table');
            if ($tables->length < 2) {
                return null;
            }
            $id = $this->find_id_from_table($tables->item(1));
            if ($id === null) {
                return null;
            }
            $float = $document->getElementById($id);
            if ($float === null) {
                return null;
            }
            $table = $this->find_measured_data_table($float);
            if ($table ===null) {
                return null;
            }
            return $this->extruct_measured_data_table($table, $date);
        }
    }
}
