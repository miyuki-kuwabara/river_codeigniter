<?php
namespace MeasuredSources\Mlitt {
    require_once APPPATH.'models/Entities/MeasuredValueTypes.php';
    require_once APPPATH.'models/MeasuredSources/Mlitt/IDataParser.php';
    require_once APPPATH.'models/MeasuredSources/Mlitt/MeasuredValuesParser.php';
    
    class LevelDataParser implements IDataParser {
        private $columns = array(
            '日付' => array(
                'key' => 'measured_at_date',
                'parser' => 'parse_date'
            ),
            '時刻' => array(
                'key' => 'measured_at_time',
                'parser' => 'parse_time'
            ),
            'データ' => array(
                'key' => 'value',
                'parser' => 'parse_numeric'
            ),
            'フラグ' => array(
                'key' => 'flags',
                'parser' => 'parse_flags'
            ),
        );

        public function parse($content, $acquired_at) {
            $datum = array();
            $indexes = null;
            $headers = array_keys($this->columns);
            $first_header = $headers[0];
            foreach (explode("\r\n", $content) as $line) {
                $columns = explode(',', $line);

                if (mb_substr($columns[0], 0, 1) == '#') {
                    $columns[0] = mb_substr($columns[0], 1);
                    if ($columns[0] === $first_header) {
                        $indexes = $this->extract_headers($columns);
                    }
                } elseif (isset($indexes)) {
                    $result = $this->extract_data($indexes, $columns, $acquired_at);
                    if (!empty($result)) $datum[] = $result;

                } else;
            }
            return $datum;
        }

        private function extract_headers($headers) {
            $columns = $this->columns;
            return array_reduce(
                $headers, 
                function ($dictionary, $header) use ($columns) {
                    $dictionary[] = array_key_exists($header, $columns)
                        ? $columns[$header]
                        : null;
                    return $dictionary;
                }, array());
        }

        private function extract_data($indexes, $columns, $acquired_at) {
            $extracted = array();
            $parser = new MeasuredValuesParser();
            
            foreach ($indexes as $i => $index) {
                if (isset($index) && isset($columns[$i])) {
                    $column = $columns[$i];
                    $extracted[$index['key']] = $parser->$index['parser']($column);
                }
            }
            
            if (!isset($extracted['measured_at_date']) || !isset($extracted['measured_at_time'])) {
                return null;
            }

            return  array(
                'measured_at' => "{$extracted['measured_at_date']} {$extracted['measured_at_time']}",
                'value_type' => \Entities\MeasuredValueTypes::WATER_LEVEL,
                'value' => $extracted['value'],
                'flags' => $extracted['flags'],
                'acquired_at' => $acquired_at,
            );
        }
    }    
}

