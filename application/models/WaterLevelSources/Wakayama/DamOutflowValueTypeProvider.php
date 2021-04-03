<?php
namespace WaterLevelSources\Wakayama {
    require_once APPPATH.'models/WaterLevelSources/Wakayama/IValueTypeProvider.php';
    
    class DamOutflowValueTypeProvider implements IValueTypeProvider {
        public function get() {
            return \Entities\MeasuredValueTypes::OUTFLOW;
        }
    }
}