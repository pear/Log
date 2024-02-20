<?php

require_once 'Log.php';

$conf = ['title' => 'Sample Log Output'];
$logger = Log::singleton('win', 'LogWindow', 'ident', $conf);
for ($i = 0; $i < 10; $i++) {
    $logger->log("Log entry $i");
}
