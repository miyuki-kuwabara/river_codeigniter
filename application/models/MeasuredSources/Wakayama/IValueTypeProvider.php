<?php
namespace MeasuredSources\Wakayama {
    require_once APPPATH.'models/Entities/MeasuredValueTypes.php';
    
    interface IValueTypeProvider
    {
        public function get();
    }
}
