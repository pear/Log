<?php
// $Id$
// $Horde: horde/lib/Log/file.php,v 1.4 2000/06/28 21:36:13 jon Exp $

/**
 * The Log_file class is a concrete implementation of the Log::
 * abstract class which writes message to a text file.
 * 
 * @author  Jon Parise <jon@php.net>
 * @version $Revision$
 * @since   Horde 1.3
 * @package Log
 */
class Log_file extends Log {

    /** 
    * String holding the filename of the logfile. 
    * @var string
    */
    var $_filename = '';

    /**
    * Integer holding the file handle. 
    * @var integer
    */
    var $_fp = 0 ;


    /**
     * Constructs a new logfile object.
     * 
     * @param string $name     The filename of the logfile.
     * @param string $ident    The identity string.
     * @param array  $conf     The configuration array.
     * @param int    $maxLevel Maximum level at which to log.
     * @access public
     */
    function Log_file($name, $ident = '', $conf = array(),
                      $maxLevel = LOG_DEBUG)
    {
        // bc compatibilty
        if( 0 == count( $conf )) {
            $conf = false ;
        }    
        $this->_filename = $name;
        $this->_ident = $ident;
        $this->_maxLevel = $maxLevel;
    }

    /**
     * Opens the logfile for appending, if it has not already been opened.
     * If the file doesn't already exist, attempt to create it.  This is
     * implicitly called by log(), if necessary.
     * @access public
     */
    function open()
    {
        if (!$this->_opened) {
            $this->_fp = fopen($this->_filename, 'a');
            $this->_opened = true;
        }
    }

    /**
     * Closes the logfile, if it is open.
     * @access public
     */
    function close()
    {
        if ($this->_opened) {
            fclose($this->_fp);
            $this->_opened = false;
        }
    }

    /**
     * Writes $message to the currently open logfile.  Calls open(), if
     * necessary.  Also, passes the message along to any Log_observer
     * instances that are observing this Log.
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

        if (!$this->_opened) {
            $this->open();
        }

        $entry = sprintf("%s %s [%s] %s\n", strftime('%b %d %H:%M:%S'),
            $this->_ident, Log::priorityToString($priority), $message);

        if ($this->_fp) {
            fwrite($this->_fp, $entry);

            /*
             * The file must be closed immediately, or we will run into
             * concurrency blocking issues.
             */
            $this->close();
        }

        $this->notifyAll(array('priority' => $priority, 'message' => $message));
    }
}

?>
