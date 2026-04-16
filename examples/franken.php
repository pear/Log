<?php

require_once 'Log.php';

$logger = Log::singleton('franken', '', 'ident');

$logger->emerg('PEAR_LOG_EMERG - System is unusable.');
$logger->alert('PEAR_LOG_ALERT - Immediate action required.');
$logger->crit('PEAR_LOG_CRIT - Critical conditions.');
$logger->err('PEAR_LOG_ERR - Error conditions.');
$logger->warning('PEAR_LOG_WARNING - Warning conditions.');
$logger->notice('PEAR_LOG_NOTICE - Normal but significant.');
$logger->info('PEAR_LOG_INFO - Informational.');
$logger->debug('PEAR_LOG_DEBUG - Debug-level messages.');
