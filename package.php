<?php

require_once 'PEAR/PackageFileManager.php';

$version = '1.8.7';
$notes = <<<EOT
The Log package now supports logging arrays with a 'message' key.
The 'display' handler now preserves line breaks in its output.
An experimental new handler ('daemon') has been added.
EOT;

$changelog = <<<EOT
The Log::_extractMessage() routine will now extract and use the value of an array's 'message' key if it exists. (Laurent Laville)
The 'display' handler now preserves line breaks in its output. (Bug 2606)
An experimental new syslog daemon log handler ('daemon') has been added to the Log distribution. (Bart van der Schans)
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
