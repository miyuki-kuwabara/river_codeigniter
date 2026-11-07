<?php
namespace MeasuredSources\Mlitt {

defined('BASEPATH') or exit('No direct script access allowed');
    require_once APPPATH.'models/HttpGetter.php';
    require_once APPPATH.'models/MeasuredSources/IMeasuredSourceCollector.php';
    require_once APPPATH.'models/MeasuredSources/Mlitt/LevelDataParser.php';
    require_once APPPATH.'models/MeasuredSources/Mlitt/DamDataParser.php';

    // 国土交通省 川の防災情報の観測所ページ(uri)のクエリから観測所を特定し、
    // ページが内部で読み込んでいるJSONから時刻値(hrValues)を収集する。
    // 水文水質データベース(www1.river.go.jp)はrobots.txtで/search/の機械的な取得を
    // 許可していないため利用しない。
    class MlittCollector implements \MeasuredSources\IMeasuredSourceCollector
    {
        const FILE_BASE_URL = 'https://www.river.go.jp/kawabou/file';

        private $source_url = null;
        private $data_parser = null;
        private $timezone = null;

        public static function create_level($source_url)
        {
            return new MlittCollector(new LevelDataParser(), $source_url);
        }

        public static function create_dam($source_url)
        {
            return new MlittCollector(new DamDataParser(), $source_url);
        }
        
        private function __construct(IDataParser $data_parser, $source_url)
        {
            $this->data_parser = $data_parser;
            $this->source_url = $source_url;
            $this->timezone = new \DateTimeZone('Asia/Tokyo');
        }

        public function get()
        {
            $observatory_code = $this->get_observatory_code();
            if ($observatory_code === null) {
                return array();
            }

            $getter = new \HttpGetter();
            $current_time = $this->get_current_time($getter);
            if ($current_time === null) {
                return array();
            }

            $url = sprintf(
                '%s/files/tmlist/%s/%s/%s.json',
                self::FILE_BASE_URL,
                $this->data_parser->get_item(),
                $current_time->format('Ymd/Hi'),
                $observatory_code);
            $json = json_decode($getter->get($url), true);
            if (!is_array($json)) {
                return array();
            }

            return $this->data_parser->parse($json, new \DateTime('now', $this->timezone));
        }

        // 事務所コード5桁 + 観測項目コード3桁 + 観測所コード5桁
        private function get_observatory_code()
        {
            $query = parse_url($this->source_url, PHP_URL_QUERY);
            if (!is_string($query)) {
                return null;
            }
            parse_str($query, $params);
            if (!isset($params['ofcCd'], $params['itmkndCd'], $params['obsCd'])) {
                return null;
            }
            return sprintf('%05d%03d%05d', $params['ofcCd'], $params['itmkndCd'], $params['obsCd']);
        }

        private function get_current_time(\HttpGetter $getter)
        {
            $response = $getter->get(self::FILE_BASE_URL.'/system/tmCrntTime.json');
            $json = json_decode($response, true);
            if (!isset($json['crntObsTime'])) {
                return null;
            }
            return MeasuredValuesParser::parse_datetime($json['crntObsTime']);
        }
    }
}
