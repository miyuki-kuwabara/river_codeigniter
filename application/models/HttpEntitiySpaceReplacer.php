<?php
class HttpEntitiySpaceReplacer {
    private $targets = null;

    public function __construct() {
        $entities = array('&nbsp;', '&emsp;', '&ensp;');
        $this->targets = array_merge(
            $entities,
            array_map(function($s) {
                return html_entity_decode($s, ENT_COMPAT, 'UTF-8');
            }, $entities));
    }

    public function replace($s) {
        return str_replace($this->targets, ' ', $s);
    }
}
