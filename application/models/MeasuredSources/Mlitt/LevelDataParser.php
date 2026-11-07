<?php
namespace MeasuredSources\Mlitt {
    require_once APPPATH.'models/Entities/MeasuredValueTypes.php';
    require_once APPPATH.'models/MeasuredSources/Mlitt/IDataParser.php';
    require_once APPPATH.'models/MeasuredSources/Mlitt/MeasuredValuesParser.php';
    
    class LevelDataParser implements IDataParser
    {
        public function get_item()
        {
            return 'stg';
        }

        public function parse($json, $acquired_at)
        {
            if (!isset($json['hrValues']) || !is_array($json['hrValues'])) {
                return array();
            }

            $datum = array();
            $parser = new MeasuredValuesParser();
            foreach ($json['hrValues'] as $row) {
                $measured_at = isset($row['obsTime']) ? $parser->parse_datetime($row['obsTime']) : null;
                if ($measured_at === null) {
                    continue;
                }

                $datum[] = array(
                    'measured_at' => $measured_at,
                    'value_type' => \Entities\MeasuredValueTypes::WATER_LEVEL,
                    'value' => isset($row['stg']) ? $parser->parse_numeric($row['stg']) : null,
                    'flags' => $parser->parse_flags(isset($row['stgCcd']) ? $row['stgCcd'] : null),
                    'acquired_at' => $acquired_at,
                );
            }
            return $datum;
        }
    }
}
