<?php

require_once 'PEAR/PackageFileManager.php';
require_once 'Console/Getopt.php';

$version = '1.8.3';
$notes = <<<EOT
Added a new handler that logs using the Sqlite extension.

The open(), close() and log() methods now consistently return success or failure.
EOT;

$changelog = <<<EOT
The open() and close() methods now consistently return success or failure.  Previously, some handlers' open() and close() methods did not return any result.  Also, the log() methods will return failure when the handler cannot be opened.

Bertrand Mansion contributed a new handler that logs using the Sqlite extension.
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
