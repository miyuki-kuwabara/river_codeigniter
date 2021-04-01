<?php
namespace WaterLevelSources\Mlitt {
    interface IDataParser {
        public function parse($content, $acquired_date);
    }    
}

