<?php
/**
 * $Header$
 *
 * @version $Revision$
 * @package Log
 */

/**
 * The Log_null class is a concrete implementation of the Log:: abstract
 * class.  It simply consumes log events.
 * 
 * @author  Jon Parise <jon@php.net>
 * @since   Log 1.8.2
 * @package Log
 *
 * @example null.php    Using the null handler.
 */
class Log_null extends Log
{
    /**
     * Constructs a new Log_null object.
     * 
     * @param string $name     Ignored.
     * @param string $ident    The identity string.
     * @param array  $conf     The configuration array.
     * @param int    $level    Log messages up to and including this level.
     * @access public
     */
    function Log_null($name, $ident = '', $conf = array(),
					  $level = PEAR_LOG_DEBUG)
    {
        $this->_id = md5(microtime());
        $this->_ident = $ident;
        $this->_mask = Log::UPTO($level);
    }

    /**
     * Simply consumes the log event.  The message will still be passed
     * along to any Log_observer instances that are observing this Log.
     * 
     * @param mixed  $message    String or object containing the message to log.
     * @param string $priority The priority of the message.  Valid
     *                  values are: PEAR_LOG_EMERG, PEAR_LOG_ALERT,
     *                  PEAR_LOG_CRIT, PEAR_LOG_ERR, PEAR_LOG_WARNING,
     *                  PEAR_LOG_NOTICE, PEAR_LOG_INFO, and PEAR_LOG_DEBUG.
     *                  The default is PEAR_LOG_INFO.
     * @return boolean  True on success or false on failure.
     * @access public
     */
    function log($message, $priority = PEAR_LOG_INFO)
    {
        /* Abort early if the priority is above the maximum logging level. */
        if (!$this->_isMasked($priority)) {
            return false;
        }

        $this->_announce(array('priority' => $priority, 'message' => $message));

        return true;
    }
}

?>