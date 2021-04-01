<?php
namespace WaterLevelSources {
    defined('BASEPATH') OR exit('No direct script access allowed');
    require_once APPPATH.'models/WaterLevelSources/Mlitt.php';
    require_once APPPATH.'models/WaterLevelSources/Araizeki.php';
    require_once APPPATH.'models/WaterLevelSources/Mlitt/LevelDataParser.php';

    class WaterLevelSourceFactory {
        public static function create_mlitt($db) {
            return new Mlitt($db, new Mlitt\LevelDataParser(), "http://www1.river.go.jp/cgi-bin/DspWaterData.exe?KIND=9&ID=306021286614020");
        }

        public static function create_araizeki($db) {
            return new Araizeki($db, "https://www.kkr.mlit.go.jp/biwako/index.php");
        }
    }
}