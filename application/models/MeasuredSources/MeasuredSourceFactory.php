<?php
namespace MeasuredSources {
    defined('BASEPATH') OR exit('No direct script access allowed');
    require_once APPPATH.'models/MeasuredSources/Mlitt.php';
    require_once APPPATH.'models/MeasuredSources/Araizeki.php';
    require_once APPPATH.'models/MeasuredSources/Wakayama.php';
    require_once APPPATH.'models/MeasuredSources/Mlitt/LevelDataParser.php';
    require_once APPPATH.'models/MeasuredSources/Mlitt/DamDataParser.php';
    require_once APPPATH.'models/MeasuredSources/Wakayama/LevelValueTypeProvider.php';
    require_once APPPATH.'models/MeasuredSources/Wakayama/DamInflowValueTypeProvider.php';
    require_once APPPATH.'models/MeasuredSources/Wakayama/DamOutflowValueTypeProvider.php';

    class MeasuredSourceFactory {
        public static function create_mlitt($db) {
            return new Mlitt($db, new Mlitt\LevelDataParser(), "http://www1.river.go.jp/cgi-bin/DspWaterData.exe?KIND=9&ID=306021286614020");
        }

        public static function create_mlitt_dam($db) {
            return new Mlitt($db, new Mlitt\DamDataParser(), 'http://www1.river.go.jp/cgi-bin/DspDamData.exe?ID=1368051260060&KIND=3');
        }

        public static function create_araizeki($db) {
            return new Araizeki($db, "https://www.kkr.mlit.go.jp/biwako/index.php");
        }

        public static function create_wakayama_level($db) {
            return new Wakayama($db, new Wakayama\LevelValueTypeProvider(), 'http://kasensabo02.pref.wakayama.lg.jp/keitai/suii/301406.html');
        }

        public static function create_wakayama_dam_inflow($db) {
            return new Wakayama($db, new Wakayama\DamInflowValueTypeProvider(), 'http://kasensabo02.pref.wakayama.lg.jp/keitai/dam/501-0.html');
        }

        public static function create_wakayama_dam_outflow($db) {
            return new Wakayama($db, new Wakayama\DamInflowValueTypeProvider(), 'http://kasensabo02.pref.wakayama.lg.jp/keitai/dam/501-1.html');
        }
    }
}