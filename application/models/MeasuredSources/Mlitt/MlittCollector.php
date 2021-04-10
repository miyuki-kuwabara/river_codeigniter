<?php
namespace MeasuredSources\Mlitt {
    defined('BASEPATH') or exit('No direct script access allowed');
    require_once APPPATH.'models/HttpGetter.php';
    require_once APPPATH.'models/HttpHeaderParser.php';
    require_once APPPATH.'models/MeasuredSources/IMeasuredSourceCollector.php';
    require_once APPPATH.'models/MeasuredSources/Mlitt/LevelDataParser.php';
    require_once APPPATH.'models/MeasuredSources/Mlitt/DamDataParser.php';

    class MlittCollector implements \MeasuredSources\IMeasuredSourceCollector
    {
        const DOWNLOAD_IMG_SRC = "download.gif";
        private $source_url = null;
        private $data_parser = null;

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
        }

        public function get()
        {
            $getter = new \HttpGetter();
            $download_url = $this->get_download_url($getter);
            if ($download_url === null) {
                return null;
            }

            $data = $this->get_level_data($getter, $download_url);
            return $data;
        }

        private function get_download_url(\HttpGetter $getter)
        {
            $response = $getter->get($this->source_url);

            libxml_use_internal_errors(true);
            
            $document = new \DOMDocument();
            $load = $document->loadHTML($response);
            if ($load === false) {
                return null;
            }

            $encoding = strtoupper($document->encoding);
            $convert = $encoding === "UTF-8"
                ? function ($src) {
                    return $src;
                }
            : function ($src) use ($encoding) {
                return mb_convert_encoding($src, "UTF-8", $encoding);
            };

            $images = $document->getElementsByTagName("img");
            $length = strlen(self::DOWNLOAD_IMG_SRC);
            foreach ($images as $image) {
                $src = $convert($image->getAttribute("src"));
                $split = substr($src, -$length);
                if (self::DOWNLOAD_IMG_SRC === $split) {
                    if ($image->parentNode === null || "DOMElement" !== get_class($image->parentNode)) {
                        return null;
                    }
                    $parent = $image->parentNode;
                    if ($parent->tagName !== "a") {
                        return null;
                    }
                    $url = $convert($parent->getAttribute("href"));
                    return $this->normalize_url($this->source_url, $url);
                }
            }
            return null;
        }

        private function normalize_url($base_url, $target_url)
        {
            if (preg_match('/^(\w+\:\/\/[^\/]+)?(\/.+)$/', $target_url, $matches)) {
                if ($matches[1] !== "") {
                    return $target_url;
                }
                if ($matches[2] !== "") {
                    if (preg_match('/^(\w+\:\/\/[^\/]+)?(\/.+)$/', $base_url, $matches2)) {
                        return $matches2[1] . $matches[2];
                    }
                }
            }

            // 相対パスかも？くっつけてみる。
            return $base_url . $target_url;
        }

        private function get_level_data(\HttpGetter $getter, $download_url)
        {
            $response =  $getter->get_with_header($download_url);
            $encoding = null;
            $timestamp = null;
            if (isset($response['header'])) {
                $header_parser = new \HttpHeaderParser($response['header']);
                $encoding = $header_parser->get_charset();
                $timestamp = $header_parser->get_last_modified() === null
                    ? $header_parser->get_last_modified()
                    : $header_parser->get_date();
            }
            $date = new \DateTime("@$timestamp");

            $content = isset($encoding)
                ? mb_convert_encoding($response['content'], 'UTF-8', $encoding)
                : $response['content'];

            return $this->data_parser->parse($content, $date);
        }
    }
}
