<?php
namespace MeasuredSources {
    defined('BASEPATH') or exit('No direct script access allowed');
    require_once APPPATH.'models/Entities/MeasuredSourceTypes.php';
    require_once APPPATH.'models/MeasuredSources/Mlitt/MlittCollector.php';
    require_once APPPATH.'models/MeasuredSources/Araizeki/AraizekiCollector.php';
    require_once APPPATH.'models/MeasuredSources/Wakayama/WakayamaCollector.php';
    require_once APPPATH.'models/MeasuredSources/Nara/NaraCollector.php';
    require_once APPPATH.'models/MeasuredSources/Gifu/GifuCollector.php';
    require_once APPPATH.'models/MeasuredSources/Aichi/AichiCollector.php';
    require_once APPPATH.'models/MeasuredSources/NormalMeasuredSourceStore.php';
    require_once APPPATH.'models/MeasuredSources/NullMeasuredSourceCollector.php';
    require_once APPPATH.'models/MeasuredSources/MeasuredSource.php';

    class MeasuredSourceFactory
    {
        /**
         * 測定値ソースを一括取得する
         *
         * @param \CI_DB $db
         * @param integer $id
         * @param integer $type
         * @param string $uri
         * @return \MeasuredSources\MeasuredSource[]
         */
        public static function create($db, $id, $type, $uri)
        {
            $collector = self::create_collector($type, $uri);
            $store = self::create_store($db, $id, $type);
            return new MeasuredSource($collector, $store);
        }

        private static function create_collector($type, $uri)
        {
            switch ($type) {
            case \Entities\MeasuredSourceTypes::MLITT_LEVEL:                  // 国土交通省水位
                return self::create_mlitt($uri);
            case \Entities\MeasuredSourceTypes::MLITT_DAM:                    // 国土交通省ダム
                return self::create_mlitt_dam($uri);
            case \Entities\MeasuredSourceTypes::WAKAYAMA_LEVEL:               // 和歌山県水位
                return self::create_wakayama_level($uri);
            case \Entities\MeasuredSourceTypes::WAKAYAMA_DAM_INFLOW:          // 和歌山県ダム流入
                return self::create_wakayama_dam_inflow($uri);
            case \Entities\MeasuredSourceTypes::WAKAYAMA_DAM_OUTFLOW:         // 和歌山県ダム放流
                return self::create_wakayama_dam_outflow($uri);
            case \Entities\MeasuredSourceTypes::ARAIZEKI:                     // 南郷洗堰
                return self::create_araizeki($uri);
            case \Entities\MeasuredSourceTypes::NARA_LEVEL:                   // 奈良県河川情報システム水位
                return self::create_nara($uri);
            case \Entities\MeasuredSourceTypes::GIFU_LEVEL:                  // 岐阜県川の防災情報水位
                return self::create_gifu($uri);
            case \Entities\MeasuredSourceTypes::AICHI_LEVEL:                 // 愛知県 川の防災情報水位
                return self::create_aichi($uri);
            case \Entities\MeasuredSourceTypes::WAKAYAMA_DAM_STORAGE_LEVEL:   // 和歌山県ダム貯水位(予約)
            case \Entities\MeasuredSourceTypes::WAKAYAMA_DAM_STORAGE_VOLUME:  // 和歌山県ダム貯水量(予約)
            default:
                return new NullMeasuredSourceCollector();
            }
        }

        private static function create_store($db, $id, $type)
        {
            //TODO: 分岐は後で
            return new NormalMeasuredSourceStore($db, $id);
        }

        private static function create_mlitt($uri)
        {
            return Mlitt\MlittCollector::create_level($uri);
        }

        private static function create_mlitt_dam($uri)
        {
            return Mlitt\MlittCollector::create_dam($uri);
        }

        private static function create_araizeki($uri)
        {
            return new Araizeki\AraizekiCollector($uri);
        }

        private static function create_wakayama_level($uri)
        {
            return Wakayama\WakayamaCollector::create_level($uri);
        }

        private static function create_wakayama_dam_inflow($uri)
        {
            return Wakayama\WakayamaCollector::create_dam_inflow($uri);
        }

        private static function create_wakayama_dam_outflow($uri)
        {
            return Wakayama\WakayamaCollector::create_dam_outflow($uri);
        }

        private static function create_nara($uri)
        {
            return new Nara\NaraCollector($uri);
        }

        private static function create_gifu($uri)
        {
            return new Gifu\GifuCollector($uri);
        }
        
        private static function create_aichi($uri)
        {
            return new Aichi\AichiCollector($uri);
        }
    }
}
