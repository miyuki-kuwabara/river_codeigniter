<?php
namespace MeasuredSources\Wakayama {
    require_once APPPATH.'models/MeasuredSources/Wakayama/IValueTypeProvider.php';
    
    class DamOutflowValueTypeProvider implements IValueTypeProvider
    {
        public function get()
        {
            return \Entities\MeasuredValueTypes::OUTFLOW;
        }
    }
}
