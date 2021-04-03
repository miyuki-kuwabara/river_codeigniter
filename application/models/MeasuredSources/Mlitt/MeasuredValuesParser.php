<?php
namespace MeasuredSources\Mlitt {
    require_once APPPATH.'models/Entities/MeasuredValueFlags.php';

    class MeasuredValuesParser {
        public static function parse_date($s) {
            if (!preg_match('/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/', $s, $matches))
                return null;
            if (!checkdate($matches[2] - 0, $matches[3] - 0, $matches[1] - 0))
                return null;
            return $s;
        }

        public static function parse_time($s) {
            if (!preg_match('/^\d{2}:\d{2}$/', $s))
                return null;
            return $s;
        }

        public static function parse_numeric($s) {
            return is_numeric($s) ? $s - 0 : null;
        }

        public static function parse_flags($input) {
            switch (trim($input)) {
                case '*':
                    return \Entities\MeasuredValueFlags::TEMPORARY;
                case '$':
                    return \Entities\MeasuredValueFlags::MISSED;
                case '#':
                    return \Entities\MeasuredValueFlags::CLOSED;
                case '-':
                    return \Entities\MeasuredValueFlags::NOT_YET;
                default:
                    return \Entities\MeasuredValueFlags::NONE;
            }
        }

        public static function parse_empty($s) {
            return $s;
        }
    }    
}

