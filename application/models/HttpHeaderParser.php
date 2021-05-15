<?php
defined('BASEPATH') or exit('No direct script access allowed');
class HttpHeaderParser
{
    private $headers = array();
    private $content_type = null;
    private $charset = null;
    private $date = null;
    private $last_modified = null;

    public function __construct($headers)
    {
        $this->headers = $headers;
        $this->parse();
    }

    private function parse()
    {
        foreach ($this->headers as $header) {
            if (preg_match('/Content-Type:\s*([^;]+);\s*charset=([-\w]+)/i', $header, $matches)) {
                $this->content_type = $matches[1];
                $this->charset = $matches[2];
            }

            if (preg_match('/Date:\s*(\S.+)$/i', $header, $matches)) {
                $timestamp = strtotime($matches[1]);
                if ($timestamp !== false && $timestamp !== -1) {
                    $this->date = $timestamp;
                }
            }

            if (preg_match('/Last-Modified:\s*(\S.+)$/i', $header, $matches)) {
                $timestamp = strtotime($matches[1]);
                if ($timestamp !== false && $timestamp !== -1) {
                    $this->last_modified = $timestamp;
                }
            }
        }
    }

    public function get_charset()
    {
        return $this->charset;
    }

    public function get_date()
    {
        return $this->date;
    }

    public function get_last_modified()
    {
        return $this->last_modified;
    }
}
