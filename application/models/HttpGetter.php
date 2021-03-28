<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class HttpGetter {

    public function get($url, $source_encoding = null) {
        $response = $this->get_with_header($url, $source_encoding);
        return $response['content'];
    }

    public function get_with_header($url, $source_encoding = null) {
        $context = stream_context_create(
            array(
                'http'	=> array(
                    'method'	=> 'GET',
                    'header'	=> 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.90 Safari/537.36',
                    'timeout'	=> 5,
                ),
            ));
        $response = file_get_contents($url, false, $context);
        $header = $http_response_header;

        if (isset($source_encoding)) 
            return array(
                'header' => $header,
                'content' => mb_convert_encoding($response, 'UTF-8', $source_encoding),
            );
        return array(
            'header' => $header,
            'content' => $response,
        );

    }
}