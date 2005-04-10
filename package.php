<?php

require_once 'PEAR/PackageFileManager.php';

$version = '1.9.0';
$notes = <<<EOT
Added stringToPriority() for converting priority names to PEAR_LOG_* constants.
The mail mail handler now uses \\r\\n instead of \\n to terminate lines.
The file handler will now perform file locking if the 'locking' parameter is set.
EOT;

$changelog = <<<EOT
Added stringToPriority() for converting priority names to PEAR_LOG_* constants. (Bug 2853)
The mail mail handler now uses \\r\\n instead of \\n to terminate lines, per RFC 821. (Bug 4107)
The file handler will now perform advsitory file locking (using flock()) if the 'locking' configuration parameter is set. (Bug 4064)
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
