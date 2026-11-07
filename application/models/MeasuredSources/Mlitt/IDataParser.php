<?php
namespace MeasuredSources\Mlitt {
    interface IDataParser
    {
        // 川の防災情報の観測項目ディレクトリ名(tmlist/{item}/...)
        public function get_item();

        public function parse($json, $acquired_at);
    }
}
