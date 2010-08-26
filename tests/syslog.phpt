--TEST--
Log: Syslog Handler
--FILE--
<?php

require_once 'Log.php';

$logger = Log::singleton('syslog', '', 'Test');
for ($i = 0; $i < 3; $i++) {
	$logger->notice("Log entry $i");
}

--EXPECT--
