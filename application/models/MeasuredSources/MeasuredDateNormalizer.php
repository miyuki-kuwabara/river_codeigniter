<?php
namespace MeasuredSources {

defined('BASEPATH') or exit('No direct script access allowed');
    class MeasuredDateNormalizer
    {
        private $year = null;
        private $month = null;
        private $day = null;
        private $hour = null;
        private $minute = null;
        private $timezone = null;

        public function __construct()
        {
            $now = new \DateTime();
            list($y, $m, $d, $h, $i, $s) = $this->extruct_datetime($now);

            $this->year = $y;
            $this->month = $m;
            $this->day = $d;
            $this->hour = $h;
            $this->minute = $i;
            $this->timezone = $now->getTimezone();
        }

        public function normalize_time($timestr)
        {
            return $this->normalize_time_internal(
                $timestr, 
                $this->year, 
                $this->month, 
                $this->day, 
                $this->hour, 
                $this->minute);
        }

        public function normalize_time_backword($timestr, \DateTime $current) {
            list($y, $m, $d, $h, $i, $s) = $this->extruct_datetime($current);
            return $this->normalize_time_internal(
                $timestr,
                $y,
                $m,
                $d,
                $h,
                $i);
        }

        public function normalize_date($datestr)
        {
            if (preg_match('/\b(?:(\d{4})\/)?\b(\d{1,2})\/(\d{1,2})\b/', $datestr, $matches)) {
                $year = $matches[1] === '' ? null : intval($matches[1]);
                $month = intval($matches[2]);
                $day = intval($matches[3]);

                $timestamp = $this->get_normalized_timestamp($year, $month, $day);
                return $this->create_from_timestamp($timestamp);
            }
            return null;
        }

        public function normalize_datetime($datetimestr)
        {
            if (preg_match('/\b(?:(\d{4})\/)?\b(\d{1,2})\/(\d{1,2})\s+(\d{1,2}):(\d{2})\b/', $datetimestr, $matches)) {
                $year = $matches[1] === '' ? null : intval($matches[1]);
                $month = intval($matches[2]);
                $day = intval($matches[3]);
                $hour = intval($matches[4]);
                $minute = intval($matches[5]);

                $timestamp = $this->get_normalized_timestamp($year, $month, $day, $hour, $minute);
                return $this->create_from_timestamp($timestamp);
            }
            return null;
        }

        private function normalize_time_internal($timestr, $y, $m, $d, $h, $i) {
            if (preg_match('/\b(\d{1,2}):(\d{2})\b/', $timestr, $matches)) {
                $hour = intval($matches[1]);
                $minute = intval($matches[2]);
                if ($h < $hour || ($h === $hour && $i < $minute)) {
                    $timestamp = mktime($hour, $minute, 0, $m, $d - 1, $y);
                } else {
                    $timestamp = mktime($hour, $minute, 0, $m, $d, $y);
                }
                return $this->create_from_timestamp($timestamp);
            }
            return null;
        }

        private function extruct_datetime(\DateTime $date_time) {
            return array_map(
                function ($s) { return intval($s); },
                explode('-', $date_time->format('Y-n-j-G-i-s')));
        }

        private function create_from_timestamp($timestamp)
        {
            $datetime = new \DateTime("@$timestamp");
            $datetime->setTimezone($this->timezone);
            return $datetime;
        }

        private function get_normalized_timestamp($year, $month, $day, $hour = 0, $minute = 0)
        {
            if (is_null($year)) {
                if ($this->month < $month || ($this->month === $month && $this->day < $day)) {
                    $year = $this->year - 1;
                } else {
                    $year = $this->year;
                }
            }
            return mktime($hour, $minute, 0, $month, $day, $year);
        }
    }
}
