<?php
// $Id$
// $Horde: horde/lib/Log/file.php,v 1.4 2000/06/28 21:36:13 jon Exp $

/**
 * The Log_file class is a concrete implementation of the Log::
 * abstract class which writes message to a text file.
 * 
 * @author  Jon Parise <jon@horde.org>
 * @version $Revision$
 * @since   Horde 1.3
 * @package Log
 */
class Log_file extends Log {

    /** 
    * String holding the filename of the logfile. 
    * @var string
    */
    var $filename = '';

    /**
    * Integer holding the file handle. 
    * @var integer
    */
    var $fp = 0 ;


    /**
     * Constructs a new logfile object.
     * 
     * @param string $log_name The filename of the logfile.
     * @param string $ident    The identity string.
     * @param array  $conf     The configuration array.
     * @access public
     */
    function Log_file($log_name, $ident = '', $conf = array())
    {
        // bc compatibilty
        if( 0 == count( $conf )) {
            $conf = false ;
        }    
        $this->filename = $log_name;
        $this->ident = $ident;
    }

    /**
     * Opens the logfile for appending, if it has not already been opened.
     * If the file doesn't already exist, attempt to create it.  This is
     * implicitly called by log(), if necessary.
     * @access public
     */
    function open()
    {
        if (!$this->opened) {
            $this->fp = fopen($this->filename, 'a');
            $this->opened = true;
        }
    }

    /**
     * Closes the logfile, if it is open.
     * @access public
     */
    function close()
    {
        if ($this->opened) {
            fclose($this->fp);
            $this->opened = false;
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
        if (!$this->opened) {
            $this->open();
        }

        $entry = sprintf("%s %s [%s] %s\n", strftime('%b %d %T'),
            $this->ident, Log::priorityToString($priority), $message);

        if ($this->fp) {
            fwrite($this->fp, $entry);

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
