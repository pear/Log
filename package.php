<?php

require_once 'PEAR/PackageFileManager.php';
require_once 'Console/Getopt.php';

$version = '1.8.4';
$notes = <<<EOT
The Log package now requires PHP 4.3.0 or later.

If an object or array is passed as a log event, its human-readable representation will be used.

It's now possible to specify the default priority for events logged using the log() method.
EOT;

$changelog = <<<EOT
The Log package now requires PHP 4.3.0 or later.

The _extractMessage() method no longer uses the serialize()'ed version of an event object if no string conversion method is available.  Instead, the human-readable (via print_r()) representation of the object will be used.

_extractMessage() can now handle arrays.  Their human-readable representation will be used.

Each Log instance now has a default priority level.  This priority will be used by the log() method when no specific priority parameter is provided.  It can be accessed using the new getPriority() and setPriority() methods.
EOT;

$package = new PEAR_PackageFileManager();

$result = $package->setOptions(array(
    'package'           => 'Log',
    'summary'           => 'Logging utilities',
    'version'           => $version,
    'state'             => 'stable',
    'license'           => 'PHP License',
    'filelistgenerator' => 'cvs',
    'ignore'            => array('package.php', 'phpdoc.sh'),
    'notes'             => $notes,
    'changelognotes'    => $changelog,
    'changelogoldtonew' => false,
	'simpleoutput'		=> true,
    'baseinstalldir'    => '/',
    'packagedirectory'  => ''));

if (PEAR::isError($result)) {
    echo $result->getMessage();
    die();
}

$package->addMaintainer('jon', 'lead', 'Jon Parise', 'jon@php.net');

$package->addDependency('php', '4.3.0', 'ge', 'php');
$package->addDependency('DB', false, 'has', 'pkg', true);
$package->addDependency('sqlite', false, 'has', 'ext', true);

if ($_SERVER['argv'][1] == 'commit') {
    $result = $package->writePackageFile();
} else {
    $result = $package->debugPackageFile();
}

if (PEAR::isError($result)) {
    echo $result->getMessage();
    die();
}
