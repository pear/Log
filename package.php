<?php

require_once 'PEAR/PackageFileManager2.php';
PEAR::setErrorHandling(PEAR_ERROR_DIE);

$desc = <<<EOT
The Log package provides an abstracted logging framework.  It includes output handlers for log files, databases, syslog, email, Firebug, and the console.  It also provides composite and subject-observer logging mechanisms.
EOT;

$version = '1.12.0a1';
$notes = <<<EOT
This release drops PHP4 compatibility (enforced by the package dependencies).  

There is unfortunately no way to support both PHP4 and PHP5 in the same code base when running under E_ALL.  Because it appears that the majority of Log package users have moved to PHP5, the Log package now targets that audience.

Given the fact that the Log package is now largely in maintenance mode, existing PHP4 users shouldn't feel adandoned.  If necessary, important fixes, etc. can be merged back into the 1.11.* release line, which will retain PHP4 compatibility.
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
$package->setPhpDep('5.0.0');
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
