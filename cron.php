<?php
define('BASE_PATH', dirname(__FILE__) . '/');
define('INDEX_PATH', BASE_PATH . 'index.php');
define('CMD', '/usr/local/php5.3/bin/php');

exec(CMD . ' ' . INDEX_PATH . ' /update');
