<?php

require_once 'PEAR/PackageFileManager.php';

$version = '1.9.1';
$notes = <<<EOT
The SQL handler failed to prepare the SQL statement when an existing database connection was provided. (Bug 5702)
The SQL handler now supports user-provided SQL insertion statements via the 'sql' configuration parameter. (Request 5703)
EOT;

$changelog = $notes;

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
$package->addDependency('DB', '1.3', 'ge', 'pkg', true);
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
