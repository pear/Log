<?php

require_once 'Log.php';

$logger = Log::singleton('null');
for ($i = 0; $i < 10; $i++) {
    $logger->log("Log entry $i");
}
