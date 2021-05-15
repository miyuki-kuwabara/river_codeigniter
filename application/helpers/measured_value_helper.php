
<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH.'models/Entities/MeasuredValueFlags.php';

if (!function_exists('is_measured_value_enable')) {
    function is_measured_value_enable($value, $flags)
    {
        return $value !== null &&
            ($flags == \Entities\MeasuredValueFLags::NONE || $flags == \Entities\MeasuredValueFLags::TEMPORARY);
    }
}

if (!function_exists('format_measured_value')) {
    function format_measured_value($value, $flags, $decimal = 2)
    {
        if (is_measured_value_enable($value, $flags)) {
            return number_format($value, $decimal);
        }
        return $decimal == 0
            ? '--'
            : '--.' . str_repeat('-', $decimal);
    }
}

if (!function_exists('measured_value')) {
    function measured_value($value, $flags, $decimal = 2)
    {
        echo format_measured_value($value, $flags, $decimal);
    }
}
