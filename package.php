<?php

require_once 'PEAR/PackageFileManager2.php';
PEAR::setErrorHandling(PEAR_ERROR_DIE);

$desc = <<<EOT
The Log package provides an abstracted logging framework.  It includes output handlers for log files, databases, syslog, email, Firebug, and the console.  It also provides composite and subject-observer logging mechanisms.
EOT;

$version = '1.11.6';
$notes = <<<EOT
The syslog handler's maximum message length is now configurable via a new 'maxLength' configuration variable.  If the handler receives a message longer than this length limit, it will be split into multiple syslog() calls.
EOT;

$package = new PEAR_PackageFileManager2();

$result = $package->setOptions(array(
    'filelistgenerator' => 'svn',
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
$package->setSummary('Logging Framework');
$package->setDescription($desc);
$package->setChannel('pear.php.net');
$package->setLicense('MIT License', 'http://www.opensource.org/licenses/mit-license.php');
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
$package->addPackageDepWithChannel('optional', 'Mail', 'pear.php.net');
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
