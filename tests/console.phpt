--TEST--
Log: Console Handler
--FILE--
<?php

require_once 'Log.php';

$conf = array('lineFormat' => '%2$s [%3$s] %4$s');
$logger = &Log::singleton('console', '', 'ident', $conf);
for ($i = 0; $i < 3; $i++) {
	$logger->log("Log entry $i");
}

--EXPECT--
ident [info] Log entry 0
ident [info] Log entry 1
ident [info] Log entry 2
