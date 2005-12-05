<?php

require_once 'PEAR/PackageFileManager2.php';
PEAR::setErrorHandling(PEAR_ERROR_DIE);

$desc = <<<EOT
The Log framework provides an abstracted logging system. It supports logging to console, file, syslog, SQL, Sqlite, mail, and mcal targets. It also provides a subject - observer mechanism.
EOT;

$version = '1.9.3';
$notes = <<<EOT
The Observer code is now PHP 4.4 compliant. (Bug 5776)
Log::factory(), Log::singleton(), and Log_observer::factory() now return null instead of false on failure.  This is more consistent with the model that these methods return object references.  Backwards compatibility is only violated if users were explicitly testing for a literal 'false' result.
EOT;

$package = new PEAR_PackageFileManager2();

$result = $package->setOptions(array(
    'filelistgenerator' => 'cvs',
    'changelogoldtonew' => false,
	'simpleoutput'		=> true,
    'baseinstalldir'    => '/',
    'packagedirectory'  => '.'));

if (PEAR::isError($result)) {
    echo $result->getMessage();
    die();
}

$package->setPackage('Log');
$package->setPackageType('php');
$package->setSummary('Logging utilities');
$package->setDescription($desc);
$package->setChannel('pear.php.net');
$package->setLicense('PHP License', 'http://www.php.net/license/3_01.txt');
$package->setAPIVersion('1.0.0');
$package->setAPIStability('stable');
$package->setReleaseVersion($version);
$package->setReleaseStability('stable');
$package->setNotes($notes);
$package->setPhpDep('4.3.0');
$package->setPearinstallerDep('1.4.3');
$package->addMaintainer('lead',  'jon', 'Jon Parise', 'jon@php.net');
$package->addIgnore(array('package.php', 'phpdoc.sh'));

$package->generateContents();
$package1 = &$package->exportCompatiblePackageFile1();

if ($_SERVER['argv'][1] == 'commit') {
    $result = $package->writePackageFile();
    $result = $package1->writePackageFile();
} else {
    $result = $package->debugPackageFile();
    $result = $package1->debugPackageFile();
}

if (PEAR::isError($result)) {
    echo $result->getMessage();
    die();
}
