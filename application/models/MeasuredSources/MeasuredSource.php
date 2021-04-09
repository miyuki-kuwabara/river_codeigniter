<?php
namespace MeasuredSources {
    defined('BASEPATH') or exit('No direct script access allowed');
    require_once APPPATH.'models/MeasuredSources/IMeasuredSourceCollector.php';
    require_once APPPATH.'models/MeasuredSources/IMeasuredSourceStore.php';

    class MeasuredSource
    {
        private $collector = null;
        private $store = null;

        public function __construct(
            IMeasuredSourceCollector $collector,
            IMeasuredSourceStore $store)
        {
            $this->collector = $collector;
            $this->store = $store;
        }

        public function update()
        {
            $this->store->store($this->collector->get());
        }
    }
}
