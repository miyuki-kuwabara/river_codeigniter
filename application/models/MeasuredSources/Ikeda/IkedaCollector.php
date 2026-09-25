<?php
namespace MeasuredSources\Ikeda {
    defined('BASEPATH') or exit('No direct script access allowed');

    require_once APPPATH.'models/Entities/MeasuredValueFlags.php';
    require_once APPPATH.'models/Entities/MeasuredValueTypes.php';
    require_once APPPATH.'models/HttpGetter.php';
    require_once APPPATH.'models/MeasuredSources/MeasuredDateNormalizer.php';
    require_once APPPATH.'models/MeasuredSources/IMeasuredSourceCollector.php';

    // 水資源機構 吉野川上流総合管理所(旧 池田総合管理所)の水管理情報ページ
    // (例: dyn/html/p0302/60/p030202.html)から、観測所ごとの24時間表(1時間間隔)を
    // 収集する。id="area-list-data"のtableに時刻・水位が並ぶ縦持ち形式で、
    // 日付は日が変わった行にしか入らないため、末尾(最新)の行から現在時刻を
    // 起点に遡って日付を推定する。
    class IkedaCollector implements \MeasuredSources\IMeasuredSourceCollector
    {
        private $source_url = null;
        private $measured_date_normalizer = null;

        public function __construct($source_url)
        {
            $this->source_url = $source_url;
            $this->measured_date_normalizer = new \MeasuredSources\MeasuredDateNormalizer();
        }

        public function get()
        {
            $getter = new \HttpGetter();
            $data = $this->get_level_data($getter);
            return $data;
        }

        private function get_level_data(\HttpGetter $getter)
        {
            $response = $getter->get($this->source_url);
            $acquired_at = new \DateTime();

            libxml_use_internal_errors(true);

            $document = new \DOMDocument();
            $load = $document->loadHTML($response);
            if ($load === false) {
                return null;
            }

            $area = $document->getElementById('area-list-data');
            if ($area === null) {
                return array();
            }

            return $this->extract($area, $acquired_at);
        }

        private function extract($area, \DateTime $acquired_at)
        {
            $datum = array();
            $current = $acquired_at;

            $rows = iterator_to_array($area->getElementsByTagName('tr'));
            foreach (array_reverse($rows) as $row) {
                $cells = $row->getElementsByTagName('td');
                if ($cells->length < 3) {
                    continue;
                }

                $measured_at = $this->measured_date_normalizer->normalize_time_backword(
                    $cells->item(1)->textContent, $current);
                if ($measured_at === null) {
                    continue;
                }
                $current = $measured_at;

                $text = trim($cells->item(2)->textContent);
                $value = is_numeric($text) ? $text - 0 : null;
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
