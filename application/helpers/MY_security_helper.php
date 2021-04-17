<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('h')) {
    /**
     * Convert special characters to HTML entities.
     *
     * @param string $value
     * @param string $encoding
     * @return void
     */
    function h($value, $encoding = 'UTF-8')
    {
        return htmlspecialchars($value, ENT_QUOTES, $encoding);
    }
}

if (!function_exists('eh')) {
    /**
     * Echo string whose special characters converted to HTML entities.
     *
     * @param string $value
     * @param string $encoding
     * @return void
     */
    function eh($value, $encoding = 'UTF-8')
    {
        echo h($value, $encoding);
    }
}
