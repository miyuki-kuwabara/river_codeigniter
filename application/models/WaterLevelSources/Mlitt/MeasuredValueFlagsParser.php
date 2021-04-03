<?php
namespace WaterLevelSources\Mlitt {
    require_once APPPATH.'models/Entities/MeasuredValueFlags.php';

    class MeasuredValueFlagsParser {
        public static function parse($input) {
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
    }    
}

