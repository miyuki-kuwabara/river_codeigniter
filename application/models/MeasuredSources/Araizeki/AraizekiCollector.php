<?php
namespace MeasuredSources\Araizeki {

defined('BASEPATH') or exit('No direct script access allowed');
    require_once APPPATH.'models/Entities/MeasuredValueTypes.php';
    require_once APPPATH.'models/Entities/MeasuredValueFlags.php';
    require_once APPPATH.'models/HttpGetter.php';
    require_once APPPATH.'models/MeasuredSources/IMeasuredSourceCollector.php';

    // 南郷洗堰全放流量を、琵琶湖水位・雨量等水管理システムのJSON(daminfo1_h.json)から収集する。
    // 5列目(ana3)が洗堰全放流量[m3/s]で、datestrに"YYYY-MM-DD HH:MM"形式の測定日時が
    // そのまま入っているため、他ソースと違い日時の遡り推定は不要。
    class AraizekiCollector implements \MeasuredSources\IMeasuredSourceCollector
    {
        const DISCHARGE_KEY = 'ana3';

        private $source_url = null;

        public function __construct($source_url)
        {
            $this->source_url = $source_url;
        }

        public function get()
        {
            $getter = new \HttpGetter();
            $response = $getter->get($this->source_url);
            $acquired_at = new \DateTime();

            // BOM付きで返ってくるため、json_decodeできるように取り除く
            $response = preg_replace('/^\xEF\xBB\xBF/', '', $response);
            $json = json_decode($response, true);
            if (!isset($json['data']) || !is_array($json['data'])) {
                return array();
            }

            return $this->extract($json['data'], $acquired_at);
        }

        private function extract($rows, \DateTime $acquired_at)
        {
            $datum = array();
            foreach ($rows as $row) {
                if (!isset($row['datestr'])) {
                    continue;
                }
                $measured_at = \DateTime::createFromFormat('Y-m-d H:i', $row['datestr']);
                if ($measured_at === false) {
                    continue;
                }

                $value = isset($row[self::DISCHARGE_KEY]) && is_numeric($row[self::DISCHARGE_KEY])
                    ? $row[self::DISCHARGE_KEY] - 0
                    : null;
                $datum[] = array(
                    'measured_at' => $measured_at,
                    'value_type' => \Entities\MeasuredValueTypes::OUTFLOW,
                    'value' => $value,
                    'flags' => isset($value) ? \Entities\MeasuredValueFlags::NONE : \Entities\MeasuredValueFlags::MISSED,
                    'acquired_at' => $acquired_at,
                );
            }
            return $datum;
        }
    }
}
