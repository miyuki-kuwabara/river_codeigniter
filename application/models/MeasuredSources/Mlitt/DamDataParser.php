<?php
namespace MeasuredSources\Mlitt {
    require_once APPPATH.'models/Entities/MeasuredValueTypes.php';
    require_once APPPATH.'models/MeasuredSources/Mlitt/IDataParser.php';
    require_once APPPATH.'models/MeasuredSources/Mlitt/MeasuredValuesParser.php';
    
    class DamDataParser implements IDataParser
    {
        private $measured_values = array(
            'allSink' => \Entities\MeasuredValueTypes::INFLOW,
            'allDisch' => \Entities\MeasuredValueTypes::OUTFLOW,
            'storPcntEff' => \Entities\MeasuredValueTypes::PERCENTAGE_OF_STORAGE,
            'storCap' => \Entities\MeasuredValueTypes::AMOUNT_OF_STORAGE,
        );

        public function get_item()
        {
            return 'dam';
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

                foreach ($this->measured_values as $key => $value_type) {
                    if (!isset($row[$key])) {
                        continue;
                    }
                    $flags_key = "{$key}Ccd";
                    $datum[] = array(
                        'measured_at' => $measured_at,
                        'value_type' => $value_type,
                        'value' => $parser->parse_numeric($row[$key]),
                        'flags' => $parser->parse_flags(isset($row[$flags_key]) ? $row[$flags_key] : null),
                        'acquired_at' => $acquired_at,
                    );
                }
            }
            return $datum;
        }
    }
}
