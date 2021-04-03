<?php
namespace WaterLevelSources\Wakayama {
    require_once APPPATH.'models/WaterLevelSources/Wakayama/IValueTypeProvider.php';
    
    class LevelValueTypeProvider implements IValueTypeProvider {
        public function get() {
            return \Entities\MeasuredValueTypes::WATER_LEVEL;
        }
    }
}