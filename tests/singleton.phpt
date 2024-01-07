--TEST--
Log: Singleton
--INI--
date.timezone=UTC
--FILE--
<?php

require_once 'Log.php';

$console1 = Log::singleton('console');
$console2 = Log::singleton('console');

if (is_a($console1, 'Log_console') && is_a($console2, 'Log_console'))
{
	echo "Two Log_console objects.\n";
}

$reflection1 = new ReflectionObject($console1);
$idProperty1 = $reflection1->getProperty('id');
$idProperty1->setAccessible(true);
$id1 = $idProperty1->getValue($console1);
$reflection2 = new ReflectionObject($console2);
$idProperty2 = $reflection2->getProperty('id');
$idProperty2->setAccessible(true);
$id2 = $idProperty2->getValue($console2);

if ($id1 == $id2) {
	echo "The objects have the same ID.\n";
}

--EXPECT--
Two Log_console objects.
The objects have the same ID.
