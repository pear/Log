<?php
// $Id$

/**
 * The Log_console class is a concrete implementation of the Log::
 * abstract class which writes message to the text console.
 * 
 * @author  Jon Parise <jon@php.net>
 * @version $Revision$
 * @package Log
 */
class Log_console extends Log
{
    /**
     * Constructs a new Log_console object.
     * 
     * @param string $name     Ignored.
     * @param string $ident    The identity string.
     * @param array  $conf     The configuration array.
     * @param array  $maxLevel Maximum priority level at which to log.
     * @access public
     */
    function Log_console($name, $ident = '', $conf = array(),
                         $maxLevel = LOG_DEBUG)
    {
        $this->_ident = $ident;
        $this->_maxLevel = $maxLevel;
    }

    /**
     * Writes $message to the text console. Also, passes the message
     * along to any Log_observer instances that are observing this Log.
     * 
     * @param string $message  The textual message to be logged.
     * @param string $priority The priority of the message.  Valid
     *                  values are: LOG_EMERG, LOG_ALERT, LOG_CRIT,
     *                  LOG_ERR, LOG_WARNING, LOG_NOTICE, LOG_INFO, and
     *                  LOG_DEBUG. The default is LOG_INFO.
     * @access public
     */
    function log($message, $priority = LOG_INFO)
    {
        /* Abort early if the priority is above the maximum logging level. */
        if ($priority > $this->_maxLevel) return;

        printf("%s %s [%s] %s\n", strftime('%b %d %T'), $this->_ident,
            Log::priorityToString($priority), $message);

        $this->notifyAll(array('priority' => $priority, 'message' => $message));
    }
}

?>
