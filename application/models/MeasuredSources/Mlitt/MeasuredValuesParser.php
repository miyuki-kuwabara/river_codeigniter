<?php
namespace MeasuredSources\Mlitt {

require_once APPPATH.'models/Entities/MeasuredValueFlags.php';

    class MeasuredValuesParser
    {
        // 川の防災情報の品質コード(xxxCcd)。0が正常で、130以上は値なしとして扱われる
        const CCD_CLOSED = 140;
        const CCD_MISSED = 190;
        const CCD_NO_DATA_FROM = 130;

        public static function parse_datetime($s)
        {
            if (!is_string($s) || !preg_match('/^\d{4}\/\d{2}\/\d{2} \d{2}:\d{2}$/', $s)) {
                return null;
            }
            return new \DateTime($s);
        }

        public static function parse_numeric($s)
        {
            return is_numeric($s) ? $s - 0 : null;
        }

        public static function parse_flags($ccd)
        {
            if ($ccd === self::CCD_CLOSED) {
                return \Entities\MeasuredValueFlags::CLOSED;
            }
            if ($ccd === self::CCD_MISSED) {
                return \Entities\MeasuredValueFlags::MISSED;
            }
            if ($ccd === null || $ccd >= self::CCD_NO_DATA_FROM) {
                return \Entities\MeasuredValueFlags::NOT_YET;
            }
            return \Entities\MeasuredValueFlags::NONE;
        }
    }
}
