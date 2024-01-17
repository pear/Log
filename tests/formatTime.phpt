--TEST--
Log: Time Format
--INI--
date.timezone=UTC
--FILE--
<?php

require_once 'Log.php';

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    echo $errstr . PHP_EOL;
}, E_USER_NOTICE|E_USER_WARNING);

$tests = [
    ['timeFormat' => 'Y-m-d H:i:s', 'timeFormatter' => null, 'expectedResult' => '2024-01-07 20:17:28'],
    ['timeFormat' => 'H:i:s', 'timeFormatter' => null, 'expectedResult' => '20:17:28'],
    ['timeFormat' => '%b %d %H:%M:%S', 'timeFormatter' => null, 'expectedResult' => 'Jan 07 20:17:28'],
    ['timeFormat' => '%O %d %a %e %A %u %w %j', 'timeFormatter' => null, 'expectedResult' => 'th 07 Sun 7 Sunday 7 0 6'],
    ['timeFormat' => '%V', 'timeFormatter' => null, 'expectedResult' => '01'],
    ['timeFormat' => '%B %m %b %-m', 'timeFormatter' => null, 'expectedResult' => 'January 01 Jan 1'],
    ['timeFormat' => '%G %Y %y', 'timeFormatter' => null, 'expectedResult' => '2024 2024 24'],
    ['timeFormat' => '%P %p %l %I %H %M %S', 'timeFormatter' => null, 'expectedResult' => 'pm PM 8 08 20 17 28'],
    ['timeFormat' => '%z %Z', 'timeFormatter' => null, 'expectedResult' => '+0000 UTC'],
    ['timeFormat' => '%s', 'timeFormatter' => null, 'expectedResult' => '1704658648'],
    ['timeFormat' => '%x %X', 'timeFormatter' => null, 'expectedResult' => '01/07/2024 20:17:28'],
    ['timeFormat' => 'Y-m-d H:i:s', 'timeFormatter' => function($timeFormat, $time) { return $time;}, 'expectedResult' => '1704658648'],
    ['timeFormat' => 'Y-m-d H:i:s', 'timeFormatter' => function($timeFormat, $time) { return $timeFormat;}, 'expectedResult' => 'Y-m-d H:i:s'],
];

foreach ($tests as $config) {
    $config['lineFormat'] = '%1$s';
    $logger = Log::factory('display', '', '', $config);
    $reflection = new ReflectionObject($logger);
    $reflectionMethod = $reflection->getMethod('formatTime');
    $reflectionMethod->setAccessible(true);
    $result = $reflectionMethod->invoke($logger, 1704658648, $config['timeFormat'], $config['timeFormatter']);
    echo $result . ' ' . ($result == $config['expectedResult'] ? 'OK' : 'FAIL') . PHP_EOL;
}

--EXPECT--
2024-01-07 20:17:28 OK
20:17:28 OK
Using strftime-style formatting is deprecated
Jan 07 20:17:28 OK
Using strftime-style formatting is deprecated
th 07 Sun 7 Sunday 7 0 6 OK
Using strftime-style formatting is deprecated
01 OK
Using strftime-style formatting is deprecated
January 01 Jan 1 OK
Using strftime-style formatting is deprecated
2024 2024 24 OK
Using strftime-style formatting is deprecated
pm PM 8 08 20 17 28 OK
Using strftime-style formatting is deprecated
+0000 UTC OK
Using strftime-style formatting is deprecated
1704658648 OK
Using strftime-style formatting is deprecated
01/07/2024 20:17:28 OK
1704658648 OK
Y-m-d H:i:s OK
