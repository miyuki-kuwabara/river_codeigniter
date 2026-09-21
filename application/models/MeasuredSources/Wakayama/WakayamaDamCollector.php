<?php
namespace MeasuredSources\Wakayama {
    defined('BASEPATH') or exit('No direct script access allowed');
    require_once APPPATH.'models/Entities/MeasuredValueTypes.php';
    require_once APPPATH.'models/Entities/MeasuredValueFlags.php';
    require_once APPPATH.'models/HttpGetter.php';
    require_once APPPATH.'models/MeasuredSources/MeasuredDateNormalizer.php';
    require_once APPPATH.'models/MeasuredSources/IMeasuredSourceCollector.php';

    // 「ダム詳細画面」(例: damDetail.html?ccd=550) が読み込む
    // 24時間表データ(hyoujidata/dam_detail_{ccd}_tbl24.csv)からダム諸量を収集する。
    // 1行1時刻の縦持ち形式で、流入量・放流量・貯水位・貯水量が同じ行に並ぶ。
    class WakayamaDamCollector implements \MeasuredSources\IMeasuredSourceCollector
    {
        const COLUMN_KANSOKU_DATE_TIME = 0;
        const COLUMN_RYUUNYUURYOU = 1; // 流入量
        const COLUMN_HOURYUURYOU = 2;  // 放流量

        private $source_url = null;
        private $value_type = null;
        private $column = null;

        public static function create_inflow($source_url)
        {
            return new WakayamaDamCollector($source_url, \Entities\MeasuredValueTypes::INFLOW, self::COLUMN_RYUUNYUURYOU);
        }

        public static function create_outflow($source_url)
        {
            return new WakayamaDamCollector($source_url, \Entities\MeasuredValueTypes::OUTFLOW, self::COLUMN_HOURYUURYOU);
        }

        private function __construct($source_url, $value_type, $column)
        {
            $this->source_url = $source_url;
            $this->value_type = $value_type;
            $this->column = $column;
        }

        public function get()
        {
            $ccd = $this->extract_ccd($this->source_url);
            if ($ccd === null) {
                return array();
            }

            $getter = new \HttpGetter();
            $response = $getter->get($this->build_csv_url($this->source_url, $ccd), 'EUC-JP');
            $acquired_at = new \DateTime();

            $lines = preg_split('/\r\n|\r|\n/', trim($response), -1, PREG_SPLIT_NO_EMPTY);
            return $this->extract($lines, $acquired_at);
        }

        private function extract_ccd($url)
        {
            $query = parse_url($url, PHP_URL_QUERY);
            if (empty($query)) {
                return null;
            }
            parse_str($query, $params);
            return isset($params['ccd']) ? $params['ccd'] : null;
        }

        private function build_csv_url($source_url, $ccd)
        {
            $parts = parse_url($source_url);
            return "{$parts['scheme']}://{$parts['host']}/hyoujidata/dam_detail_{$ccd}_tbl24.csv";
        }

        private function extract($lines, \DateTime $acquired_at)
        {
            $normalizer = new \MeasuredSources\MeasuredDateNormalizer();
            $datum = array();
            $date = $acquired_at;

            for ($i = count($lines) - 1; $i >= 0; $i--) {
                $columns = str_getcsv($lines[$i]);
                if (!isset($columns[self::COLUMN_KANSOKU_DATE_TIME]) || !isset($columns[$this->column])) {
                    continue;
                }

                $measured_at = $normalizer->normalize_day_time_backword($columns[self::COLUMN_KANSOKU_DATE_TIME], $date);
                if ($measured_at === null) {
                    continue;
                }
                $date = $measured_at;

                $value = $this->extract_value($columns[$this->column]);
                $datum[] = array(
                    'measured_at' => $measured_at,
                    'value_type' => $this->value_type,
                    'value' => $value,
                    'flags' => isset($value) ? \Entities\MeasuredValueFlags::NONE : \Entities\MeasuredValueFlags::MISSED,
                    'acquired_at' => $acquired_at,
                );
            }
            return $datum;
        }

        // 値は"14.8m3/s↓"のように単位・増減記号付きで入っているため、先頭の数値のみ取り出す。
        private function extract_value($text)
        {
            if (preg_match('/^(-?\d+(?:\.\d+)?)/', trim($text), $matches)) {
                return $matches[1] - 0;
            }
            return null;
        }
    }
}
