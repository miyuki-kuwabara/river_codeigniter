<?php
namespace MeasuredSources\Wakayama {
    defined('BASEPATH') or exit('No direct script access allowed');
    require_once APPPATH.'models/Entities/MeasuredValueTypes.php';
    require_once APPPATH.'models/Entities/MeasuredValueFlags.php';
    require_once APPPATH.'models/HttpGetter.php';
    require_once APPPATH.'models/MeasuredSources/MeasuredDateNormalizer.php';
    require_once APPPATH.'models/MeasuredSources/IMeasuredSourceCollector.php';

    // 「水位詳細画面」(例: suiiDetail.html?ccd=503) が読み込む
    // 24時間表データ(hyoujidata/suii_detail_{ccd}_tbl24.csv)から水位を収集する。
    // 1行目に時刻(◯◯時)、2行目に水位が並ぶ横持ち形式。
    class WakayamaLevelCollector implements \MeasuredSources\IMeasuredSourceCollector
    {
        private $source_url = null;

        public function __construct($source_url)
        {
            $this->source_url = $source_url;
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

            $lines = preg_split('/\r\n|\r|\n/', trim($response));
            if (count($lines) < 2) {
                return array();
            }

            return $this->extract(str_getcsv($lines[0]), str_getcsv($lines[1]), $acquired_at);
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
            return "{$parts['scheme']}://{$parts['host']}/hyoujidata/suii_detail_{$ccd}_tbl24.csv";
        }

        private function extract($hours, $values, \DateTime $acquired_at)
        {
            $normalizer = new \MeasuredSources\MeasuredDateNormalizer();
            $datum = array();
            $date = $acquired_at;

            for ($i = min(count($hours), count($values)) - 1; $i >= 0; $i--) {
                if (!preg_match('/(\d{1,2})時/', $hours[$i], $matches)) {
                    continue;
                }
                $measured_at = $normalizer->normalize_time_backword("{$matches[1]}:00", $date);
                if ($measured_at === null) {
                    continue;
                }
                $date = $measured_at;

                $value = is_numeric($values[$i]) ? $values[$i] - 0 : null;
                $datum[] = array(
                    'measured_at' => $measured_at,
                    'value_type' => \Entities\MeasuredValueTypes::WATER_LEVEL,
                    'value' => $value,
                    'flags' => isset($value) ? \Entities\MeasuredValueFlags::NONE : \Entities\MeasuredValueFlags::MISSED,
                    'acquired_at' => $acquired_at,
                );
            }
            return $datum;
        }
    }
}
