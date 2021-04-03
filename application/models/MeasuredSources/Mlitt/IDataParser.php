<?php
namespace MeasuredSources\Mlitt {
    interface IDataParser {
        public function parse($content, $acquired_date);
    }    
}

