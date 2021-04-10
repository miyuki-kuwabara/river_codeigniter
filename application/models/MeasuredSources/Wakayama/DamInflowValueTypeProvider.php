<?php
namespace MeasuredSources\Wakayama {
    require_once APPPATH.'models/MeasuredSources/Wakayama/IValueTypeProvider.php';
    
    class DamInflowValueTypeProvider implements IValueTypeProvider
    {
        public function get()
        {
            return \Entities\MeasuredValueTypes::INFLOW;
        }
    }
}
