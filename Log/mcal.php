<?php
// $Id$
// $Horde: horde/lib/Log/mcal.php,v 1.2 2000/06/28 21:36:13 jon Exp $

/**
 * The Log_mcal class is a concrete implementation of the Log::
 * abstract class which sends messages to a local or remote calendar
 * store accessed through MCAL.
 * 
 * @author  Chuck Hagenbuch <chuck@horde.org>
 * @version $Revision$
 * @since Horde 1.3
 * @package Log 
 */
class Log_mcal extends Log {

    /**
    * holding the calendar specification to connect to. 
    * @var string
    */
    var $calendar = '{localhost/mstore}';

    /** 
    * holding the username to use. 
    * @var string
    */
    var $username = '';

    /** 
    * holding the password to use. 
    * @var string
    */
    var $password = '';

    /** 
    * holding the options to pass to the calendar stream. 
    * @var integer
    */
    var $options = 0;

    /** 
    * ResourceID of the MCAL stream. 
    * @var string
    */
    var $stream = '';

    /** 
    * Integer holding the log facility to use. 
    * @var string
    */
    var $name = LOG_SYSLOG;


    /**
     * Constructs a new Log_mcal object.
     * 
     * @param string $log_name The category to use for our events.
     * @param string $ident    The identity string.
     * @param array  $conf     The configuration array.
     * @access public
     */
    function Log_mcal($log_name = LOG_SYSLOG, $ident = '', $conf = array())
    {
        // bc compatibilty
        if( 0 == count( $conf )) {
            $conf = false ;
        }
        $this->name = $log_name;
        $this->_ident = $ident;
        $this->calendar = $conf['calendar'];
        $this->username = $conf['username'];
        $this->password = $conf['password'];
        $this->options = $conf['options'];
    }

    /**
     * Opens a calendar stream, if it has not already been
     * opened. This is implicitly called by log(), if necessary.
     * @access public
     */
    function open()
    {
        if (!$this->_opened) {
            $this->stream = mcal_open($this->calendar, $this->username, $this->password, $this->options);
            $this->_opened = true;
        }
    }

    /**
     * Closes the calendar stream, if it is open.
     * @access public
     */
    function close()
    {
        if ($this->_opened) {
            mcal_close($this->stream);
            $this->_opened = false;
        }
    }

    /**
     * Logs $message and associated information to the currently open
     * calendar stream. Calls open() if necessary. Also passes the
     * message along to any Log_observer instances that are observing
     * this Log.
     * 
     * @param string $message  The textual message to be logged.
     * @param string $priority The priority of the message. Valid
     *                  values are: LOG_EMERG, LOG_ALERT, LOG_CRIT,
     *                  LOG_ERR, LOG_WARNING, LOG_NOTICE, LOG_INFO,
     *                  and LOG_DEBUG. The default is LOG_INFO.
     * @access public
     */
    function log($message, $priority = LOG_INFO)
    {
        if (!$this->_opened) {
            $this->open();
        }

        $date_str = date('Y:n:j:G:i:s');
        $dates = explode(':', $date_str);

        mcal_event_init($this->stream);
        mcal_event_set_title($this->stream, $this->_ident);
        mcal_event_set_category($this->stream, $this->name);
        mcal_event_set_description($this->stream, $message);
        mcal_event_add_attribute($this->stream, 'priority', $priority);
        mcal_event_set_start($this->stream, $dates[0], $dates[1], $dates[2], $dates[3], $dates[4], $dates[5]);
        mcal_event_set_end($this->stream, $dates[0], $dates[1], $dates[2], $dates[3], $dates[4], $dates[5]);
        mcal_append_event($this->stream);

        $this->notifyAll(array('priority' => $priority, 'message' => $message));
    }
}

?>
