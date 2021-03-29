<?php
namespace WaterLevelSources {
    defined('BASEPATH') OR exit('No direct script access allowed');
    require_once APPPATH.'models/WaterLevelSources/Mlitt.php';

    class WaterLevelSourceFactory {
        public static function create_mlitt($db) {
            return new \WaterLevelSources\Mlitt($db, "http://www1.river.go.jp/cgi-bin/DspWaterData.exe?KIND=9&ID=306021286614020");
        }
    }
}