<?php
namespace WaterLevelSources\Mlitt {
    require_once APPPATH.'models/WaterLevelSources/Mlitt/IDataParser.php';
    
    class LevelDataParser implements IDataParser {
        public function parse($content, $acquired_date) {
            $data = array();
            foreach (explode("\r\n", $content) as $line) {
                $columns = explode(',', $line);
                if (!preg_match('/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/', $columns[0], $date))
                    continue;
                if (!checkdate($date[2] - 0, $date[3] - 0, $date[1] - 0))
                    continue;
                if (!preg_match('/^\d{2}:\d{2}$/', $columns[1]))
                    continue;
                $level = is_numeric($columns[2]) ? $columns[2] - 0 : null;
                $data[] = array(
                    'date' => "{$columns[0]} {$columns[1]}",
                    'level' => $level,
                    'acquired' => $acquired_date, 
                );
            }
            return $data;
        }
    }    
}

