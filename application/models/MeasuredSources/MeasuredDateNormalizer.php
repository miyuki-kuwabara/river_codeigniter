<?php
namespace MeasuredSources {
    defined('BASEPATH') OR exit('No direct script access allowed');
    class MeasuredDateNormalizer {
        private $year = null;
        private $month = null;
        private $day = null;
        private $hour = null;
        private $minute = null;

        public function __construct() {
            $now = localtime(time(), true);
            $this->year = $now['tm_year'] + 1900;
            $this->month = $now['tm_mon'] + 1;
            $this->day = $now['tm_mday'];
            $this->hour = $now['tm_hour'];
            $this->minute = $now['tm_min'];
        }

        public function normalize_time($timestr) {
            if (preg_match('/\b(\d{1,2}):(\d{2})\b/', $timestr, $matches)) {
                $hour = intval($matches[1]);
                $minute = intval($matches[2]);
                if ($this->hour < $hour || ($this->hour === $hour && $this->minute < $minute)) {
                    $timestamp = mktime($hour, $minute, 0, $this->month, $this->day - 1, $this->year);
                } else {
                    $timestamp = mktime($hour, $minute, 0, $this->month, $this->day, $this->year);
                }
                return date('Y-m-d H:i', $timestamp);
            }
            return null;
        }

        public function normalize_date($datestr) {
            if (preg_match('/\b(?:(\d{4})\/)?\b(\d{1,2})\/(\d{1,2})\b/', $datestr, $matches)) {
                $year = $matches[1] === '' ? null : intval($matches[1]);
                $month = intval($matches[2]);
                $day = intval($matches[3]);

                $timestamp = $this->get_normalized_timestamp($year, $month, $day);
                return date('Y-m-d', $timestamp);
            }
            return null;
        }

        public function normalize_datetime($datetimestr) {
            if (preg_match('/\b(?:(\d{4})\/)?\b(\d{1,2})\/(\d{1,2})\s+(\d{1,2}):(\d{2})\b/', $datetimestr, $matches)) {
                $year = $matches[1] === '' ? null : intval($matches[1]);
                $month = intval($matches[2]);
                $day = intval($matches[3]);
                $hour = intval($matches[4]);
                $minute = intval($matches[5]);

                $timestamp = $this->get_normalized_timestamp($year, $month, $day, $hour, $minute);
                return date('Y-m-d H:i', $timestamp);
            }
            return null;
        }

        private function get_normalized_timestamp($year, $month, $day, $hour = 0, $minute = 0) {
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