<?php
namespace MeasuredSources {
    defined('BASEPATH') OR exit('No direct script access allowed');
    class MeasuredDateNormalizer {
        private $now = null;

        public function __constract() {
            $this->now = localtime(time(), true);
        }

        public function normalize_time($timestr) {
            if (preg_match('/(\d{1,2}):(\d{2})/', $timestr, $matches)) {
                $hour = intval($matches[1]);
                $minute = intval($matches[2]);
                if ($this->now['tm_hour'] < $hour || ($this->now['tm_hour'] === $hour && $this->now['tm_min'] < $minute)) {
                    $timestamp = mktime($hour, $minute, 0, $this->now['tm_mon'] + 1, $this->now['tm_mday'] - 1, $this->now['tm_year'] + 1900);
                } else {
                    $timestamp = mktime($hour, $minute, 0, $this->now['tm_mon'] + 1, $this->now['tm_mday'], $this->now['tm_year'] + 1900);
                }
                return date('Y-m-d H:i', $timestamp);
            }
            return null;
        }
    }
}