<?php
namespace MeasuredSources {
    defined('BASEPATH') OR exit('No direct script access allowed');
    require_once APPPATH.'models/MeasuredSources/Mlitt/MlittCollector.php';
    require_once APPPATH.'models/MeasuredSources/Araizeki/AraizekiCollector.php';
    require_once APPPATH.'models/MeasuredSources/Wakayama/WakayamaCollector.php';
    require_once APPPATH.'models/MeasuredSources/Nara/NaraCollector.php';
    require_once APPPATH.'models/MeasuredSources/Gifu/GifuCollector.php';

    class MeasuredSourceFactory {
        public static function create_mlitt($db) {
            return Mlitt\MlittCollector::create_level($db, "http://www1.river.go.jp/cgi-bin/DspWaterData.exe?KIND=9&ID=306021286614020");
        }

        public static function create_mlitt_dam($db) {
            return Mlitt\MlittCollector::create_dam($db, 'http://www1.river.go.jp/cgi-bin/DspDamData.exe?ID=1368051260060&KIND=3');
        }

        public static function create_araizeki($db) {
            return new Araizeki\AraizekiCollector($db, "https://www.kkr.mlit.go.jp/biwako/index.php");
        }

        public static function create_wakayama_level($db) {
            return Wakayama\WakayamaCollector::create_level($db, 'http://kasensabo02.pref.wakayama.lg.jp/keitai/suii/301406.html');
        }

        public static function create_wakayama_dam_inflow($db) {
            return Wakayama\WakayamaCollector::create_dam_inflow($db, 'http://kasensabo02.pref.wakayama.lg.jp/keitai/dam/501-0.html');
        }

        public static function create_wakayama_dam_outflow($db) {
            return Wakayama\WakayamaCollector::create_dam_outflow($db, 'http://kasensabo02.pref.wakayama.lg.jp/keitai/dam/501-1.html');
        }

        public static function create_nara($db) {
            return new Nara\NaraCollector($db, 'http://www.kasen.pref.nara.jp/sppub/status/river_log_1_131.html');
        }

        public static function create_gifu($db) {
            return new Gifu\GifuCollector($db, 'http://www.kasen.pref.gifu.lg.jp/h/Valley_6_382.html');
        }
    }
}