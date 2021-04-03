<?php
namespace WaterLevelSources\Mlitt {
    require_once APPPATH.'models/Entities/MeasuredValueTypes.php';
    require_once APPPATH.'models/WaterLevelSources/Mlitt/IDataParser.php';
    require_once APPPATH.'models/WaterLevelSources/Mlitt/MeasuredValueFlagsParser.php';
    
    class DamDataParser implements IDataParser {
        private $columns = array(
            '年月日' => array(
                'key' => 'measured_at_date',
                'parser' => 'parse_date' ,
            ),
            '時刻' => array(
                'key' => 'measured_at_time',
                'parser' => 'parse_time',
            ),
            '流域平均雨量' => null,
            '流域平均雨量属性' =>  null,
            '貯水量' => array(
                'key' => 'amount_of_storage',
                'parser' => 'parse_numeric',
            ),
            '貯水量属性' => array(
                'key' => 'amount_of_storage_flags',
                'parser' => 'parse_flags',
            ),
            '流入量' => array(
                'key' => 'inflow',
                'parser' => 'parse_numeric',
            ),
            '流入量属性' => array(
                'key' => 'inflow_flags',
                'parser' => 'parse_flags',
            ),
            '放流量' => array(
                'key' => 'outflow',
                'parser' => 'parse_numeric',
            ),
            '放流量属性' => array(
                'key' => 'outflow_flags',
                'parser' => 'parse_flags',
            ),
            '貯水率' => array(
                'key' => 'parcentage_of_storage',
                'parser' => 'parse_numeric',
            ),
            '貯水率属性' => array(
                'key' => 'parcentage_of_storage_flags',
                'parser' => 'parse_flags',
            ),
        );

        private $measured_values = array(
            'inflow' => \Entities\MeasuredValueTypes::INFLOW,
            'outflow' => \Entities\MeasuredValueTypes::OUTFLOW,
            'percentage_of_storage' => \Entities\MeasuredValueTypes::PERCENTAGE_OF_STORAGE,
            'amount_of_storage' => \Entities\MeasuredValueTypes::AMOUNT_OF_STORAGE,
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
                    if (!empty($result)) $datum = array_merge($datum, $result);

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
            foreach ($indexes as $i => $index) {
                if (isset($index) && isset($columns[$i])) {
                    $column = $columns[$i];
                    $extracted[$index['key']] = call_user_func(array(__CLASS__, $index['parser']), $column);
                }
            }
            if (isset($extracted['measured_at_date']) && isset($extracted['measured_at_time'])) {
                $measured_at = "{$extracted['measured_at_date']} {$extracted['measured_at_time']}";
            }
            unset($extracted['measured_at_date']);
            unset($extracted['measured_at_time']);
            $datum = array();
            foreach ($this->measured_values as $key => $value_type) {
                if (isset($extracted[$key])) {
                    $flags_key = "{$key}_flags";
                    $data = array(
                        'measured_at' => $measured_at,
                        'value_type' => $value_type,
                        'value' => $extracted[$key],
                        'flags' => isset($extracted[$flags_key]) ? $extracted[$flags_key] : 0,
                        'acquired_at' => $acquired_at,
                    );
                    $datum[] = $data;
                }
            }
            return $datum;
        }

        private static function parse_date($s) {
            if (!preg_match('/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/', $s, $matches))
                return null;
            if (!checkdate($matches[2] - 0, $matches[3] - 0, $matches[1] - 0))
                return null;
            return $s;
        }

        private static function parse_time($s) {
            if (!preg_match('/^\d{2}:\d{2}$/', $s))
                return null;
            return $s;
        }

        private static function parse_numeric($s) {
            return is_numeric($s) ? $s - 0 : null;
        }

        private static function parse_flags($s) {
            return MeasuredValueFlagsParser::parse($s);
        }

        private static function parse_empty($s) {
            return $s;
        }
    }    
}

