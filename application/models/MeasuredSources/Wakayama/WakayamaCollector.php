<?php
namespace MeasuredSources\Wakayama {
    defined('BASEPATH') or exit('No direct script access allowed');
    require_once APPPATH.'models/MeasuredSources/Wakayama/WakayamaLevelCollector.php';
    require_once APPPATH.'models/MeasuredSources/Wakayama/WakayamaDamCollector.php';

    class WakayamaCollector
    {
        public static function create_level($source_url)
        {
            return new WakayamaLevelCollector($source_url);
        }

        public static function create_dam_inflow($source_url)
        {
            return WakayamaDamCollector::create_inflow($source_url);
        }

        public static function create_dam_outflow($source_url)
        {
            return WakayamaDamCollector::create_outflow($source_url);
        }
    }
}
