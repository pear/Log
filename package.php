<?php

require_once 'PEAR/PackageFileManager2.php';
PEAR::setErrorHandling(PEAR_ERROR_DIE);

$desc = <<<EOT
The Log framework provides an abstracted logging system. It supports logging to console, file, syslog, SQL, Sqlite, mail, and mcal targets. It also provides a subject - observer mechanism.
EOT;

$version = '1.9.10';
$notes = <<<EOT
The syslog handler now has an 'inherit' configuration parameter which controls whether or openlog() is called to create a new syslog environment. (Bug 9822)
fflush() is now always called with a valid resource. (Bug 9388)
The mail handler, when composited, could send empoty messages. (Bug 8865)
EOT;

$package = new PEAR_PackageFileManager2();

$result = $package->setOptions(array(
    'filelistgenerator' => 'cvs',
    'changelogoldtonew' => false,
    'simpleoutput'		=> true,
    'baseinstalldir'    => '/',
    'packagefile'       => 'package.xml',
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
$package->addMaintainer('lead', 'jon', 'Jon Parise', 'jon@php.net');
$package->addMaintainer('lead', 'chagenbu', 'Chuck Hagenbuch', 'chuck@horde.org');
$package->addMaintainer('lead', 'yunosh', 'Jan Schneider', 'jan@horde.org');
$package->addIgnore(array('package.php', 'phpdoc.sh', 'package.xml'));
$package->addPackageDepWithChannel('optional', 'DB', 'pear.php.net', '1.3');
$package->addPackageDepWithChannel('optional', 'MDB2', 'pear.php.net', '2.0.0RC1');
$package->addExtensionDep('optional', 'sqlite');

$package->generateContents();

if ($_SERVER['argv'][1] == 'commit') {
    $result = $package->writePackageFile();
} else {
    $result = $package->debugPackageFile();
}

if (PEAR::isError($result)) {
    echo $result->getMessage();
    die();
}
