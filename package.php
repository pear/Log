<?php

require_once 'PEAR/PackageFileManager.php';
require_once 'Console/Getopt.php';

$version = '1.8.0';
$notes = <<<EOT
The Log package now includes a users guide and example scripts.

A number of small improvements have been made to the 'win' log handler (based on suggestions from Paul Yanchenko).

A new 'display' log handler has been added to the distribution.  Contributed by Paul Yanchenko, this handler simply prints the error message back to the browser.  It respects the 'error_prepend_string' and 'error_append_string' PHP INI values and is useful when using PEAR::setErrorHandling()'s PEAR_ERROR_CALLBACK mechanism.
EOT;

$description =<<<EOT
The Log framework provides an abstracted logging system.  It supports logging to console, file, syslog, SQL, mail, and mcal targets.  It also provides a subject - observer mechanism.
EOT;

$package = new PEAR_PackageFileManager();

$result = $package->setOptions(array(
    'package'           => 'Log',
    'summary'           => 'Logging utilities',
    'description'       => $description,
    'version'           => $version,
    'state'             => 'stable',
    'license'           => 'PHP License',
    'filelistgenerator' => 'cvs',
    'ignore'            => array('package.php', 'package.xml'),
    'notes'             => $notes,
    'changelogoldtonew' => false,
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
