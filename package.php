<?php

require_once 'PEAR/PackageFileManager.php';

$version = '1.9.0';
$notes = <<<EOT
Added stringToPriority() for converting priority names to PEAR_LOG_* constants.
The mail handler now uses \\r\\n instead of \\n to terminate lines.
The file handler will now perform file locking if the 'locking' parameter is set.
The file hander's directory creation routines have been rewritten.
The file handler will now set a created directory's permission mode based on the 'dirmode' parameter.
The SQL handler can now be configured with an array of DB 'options'.
The SQL handler now uses prepared queries for improved performance.
An MDB2 database handler has been added.
Only variable references should be returned by reference in Log::factory().
The file handler can now handle file modes given as strings.
The display handler now offers a 'linebreak' configuration option.
The composite handler's getIdent() method now returns the correct value.
The file handler now only attempts to set the log file's mode if it created it.
The factory() method will no longer attempt to include the handler file if the handler class has already been defined.
EOT;

$changelog = <<<EOT
Added stringToPriority() for converting priority names to PEAR_LOG_* constants. (Bug 2853)
The mail handler now uses \\r\\n instead of \\n to terminate lines, per RFC 821. (Bug 4107)
The file handler will now perform advsitory file locking (using flock()) if the 'locking' configuration parameter is set. (Bug 4064)
The file hander's directory creation routines have been rewritten. (Bug 3989)
The file handler will now set a created directory's permission mode based on the 'dirmode' parameter. (Bug 4114)
The SQL handler can now be configured with an array of DB 'options'.
The SQL handler now uses prepared queries for improved performance.
An MDB2 database handler has been added. (Lukas Smith)
Only variable references should be returned by reference in Log::factory(). (Bug 4768)
The file handler can now handle file modes given as strings. (Bug 4948)
The display handler now offers a 'linebreak' configuration option. (Bug 5014)
The composite handler's getIdent() method now returns the correct value. (Bug 5192)
The file handler now only attempts to set the log file's mode if it created it. (Bug 5273, 5418)
The factory() method will no longer attempt to include the handler file if the handler class has already been defined. (Bug 5182)
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
