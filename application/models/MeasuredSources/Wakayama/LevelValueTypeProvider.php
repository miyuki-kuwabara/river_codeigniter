<?php
namespace MeasuredSources\Wakayama {
    require_once APPPATH.'models/MeasuredSources/Wakayama/IValueTypeProvider.php';
    
    class LevelValueTypeProvider implements IValueTypeProvider {
        public function get() {
            return \Entities\MeasuredValueTypes::WATER_LEVEL;
        }
    }
}