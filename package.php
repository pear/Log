<?php

require_once 'PEAR/PackageFileManager.php';
require_once 'Console/Getopt.php';

$version = '1.8.2';
$notes = <<<EOT
Added a new 'null' log handler.
Added a flush() method to the public Log API.
EOT;

$changelog = <<<EOT
A new 'null' log handler that simply consumes log events has been added.  The 'null' handler will still respect log masks and attached observers.

The public Log API has grown a flush() method.  This allows buffered log output to be explicitly flushed.  At this time, the flush() method is only implemented by the console, file and mail handlers.

New unit tests for the Factory and Singleton construction methods have been added.
EOT;

$package = new PEAR_PackageFileManager();

$result = $package->setOptions(array(
    'package'           => 'Log',
    'summary'           => 'Logging utilities',
    'version'           => $version,
    'state'             => 'stable',
    'license'           => 'PHP License',
    'filelistgenerator' => 'cvs',
    'ignore'            => array('package.php'),
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

$package->addDependency('DB', false, 'has', 'pkg', true);

if ($_SERVER['argv'][1] == 'commit') {
    $result = $package->writePackageFile();
} else {
    $result = $package->debugPackageFile();
}

if (PEAR::isError($result)) {
    echo $result->getMessage();
    die();
}
