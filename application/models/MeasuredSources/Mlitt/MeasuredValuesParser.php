<?php
namespace MeasuredSources\Mlitt {
    require_once APPPATH.'models/Entities/MeasuredValueFlags.php';

    class MeasuredValuesParser
    {
        public static function parse_date($s)
        {
            if (!preg_match('/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/', $s, $matches)) {
                return null;
            }
            $year = $matches[1] - 0;
            $month = $matches[2] - 0;
            $day = $matches[3] - 0;

            if (!checkdate($month, $day, $year)) {
                return null;
            }
            
            return array($year, $month, $day);
        }

        public static function parse_time($s)
        {
            if (!preg_match('/^(\d{2}):(\d{2})$/', $s, $matches)) {
                return null;
            }
            return array($matches[1] - 0, $matches[2] - 0);
        }

        public static function make_date_from_parsed($date_array, $time_array)
        {
            return date('Y-m-d H:i', mktime(
                $time_array[0],
                $time_array[1],
                0,
                $date_array[1],
                $date_array[2],
                $date_array[0]));
        }

        public static function parse_numeric($s)
        {
            return is_numeric($s) ? $s - 0 : null;
        }

        public static function parse_flags($input)
        {
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

        public static function parse_empty($s)
        {
            return $s;
        }
    }
}
