--TEST--
Log: Line Format
--FILE--
<?php

require_once 'Log.php';

$conf = array('timeFormat' => '%T', 'lineFormat' => '%{timestamp} %{ident} %{priority} %{message} %{file} %{line} %{function} %{class}');
$logger = &Log::singleton('console', '', 'ident', $conf);
$logger->log('Message');

--EXPECTREGEX--
^\d{2}:\d{2}:\d{2} ident info Message .*format\.php \d+ [\(\)\w]+$
