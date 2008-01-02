--TEST--
Log: Display Handler
--FILE--
<?php

require_once 'Log.php';

function test($name, $conf)
{
    echo "\nTesting $name Configuration\n";
    echo "------------------------------------------------------\n";

    $logger = &Log::factory('display', '', $name, $conf);

    $logger->log("Info", PEAR_LOG_INFO);
    $logger->log("Error", PEAR_LOG_ERR);
    $logger->log("Debug", PEAR_LOG_DEBUG);
    $logger->log("Multi\nLine\nEntry", PEAR_LOG_INFO);

    echo "\n";
}

test('Default', array());
test('Line Break', array('linebreak' => "\n"));
test('Format', array('lineFormat' => '<!-- %4$s -->'));
test('Prepend / Append', array('error_prepend' => '<tt>',
                               'error_append' => '</tt>'));

--EXPECT--
Testing Default Configuration
------------------------------------------------------
<b>info</b>: Info<b>error</b>: Error<b>debug</b>: Debug<b>info</b>: Multi<br />
Line<br />
Entry

Testing Line Break Configuration
------------------------------------------------------
<b>info</b>: Info
<b>error</b>: Error
<b>debug</b>: Debug
<b>info</b>: Multi<br />
Line<br />
Entry


Testing Format Configuration
------------------------------------------------------
<!-- Info --><!-- Error --><!-- Debug --><!-- Multi<br />
Line<br />
Entry -->

Testing Prepend / Append Configuration
------------------------------------------------------
<tt><b>info</b>: Info</tt><tt><b>error</b>: Error</tt><tt><b>debug</b>: Debug</tt><tt><b>info</b>: Multi<br />
Line<br />
Entry</tt>
