<?php

require_once 'PEAR/PackageFileManager.php';
require_once 'Console/Getopt.php';

$version = '1.8.6';
$notes = <<<EOT
The maximum length of the 'sql' handler's 'ident' string is now configurable.
EOT;

$changelog = <<<EOT
The maximum length of the 'sql' handler's 'ident' string is now configurable via the 'identLimit' configuration parameter. (Bug 2137)
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
