<?php
// $Id$
// $Horde: horde/lib/Log.php,v 1.15 2000/06/29 23:39:45 jon Exp $

/**
 * The Log:: class implements both an abstraction for various logging
 * mechanisms and the Subject end of a Subject-Observer pattern.
 *
 * @author  Chuck Hagenbuch <chuck@horde.org>
 * @author  Jon Parise <jon@csh.rit.edu>
 * @version $Revision$
 * @since   Horde 1.3
 * @package Log
 */
class Log {

    /**
    * indicating whether or not the log connection is currently open.
    * @var boolean
    */
    var $opened = false;

    /** 
    * holding the identifier 
    * that will be stored along with each logged message. 
    * @var string
    */
    var $ident = '';

    /** 
    * holding all Log_observer objects 
    * that wish to be notified of any messages that we handle. 
    * @var array
    */
    var $listeners = array();


    /**
     * Attempts to return a concrete Log instance of $log_type.
     * 
     * @param string $log_type  The type of concrete Log subclass to return.
     *                  Attempt to dynamically include the code for this
     *                  subclass. Currently, valid values are 'syslog',
     *                  'sql', 'file', and 'mcal'.
     *
     * @param string $log_name  The name of the actually log file,
     *                  table, or other specific store to use. Defaults
     *                  to an empty string, with which the subclass will
     *                  attempt to do something intelligent.
     *
     * @param string $ident The indentity reported to the log system.
     *
     * @param array  $conf  A hash containing any additional
     *                  configuration information that a subclass might need.
     * 
     * @return object Log   The newly created concrete Log instance, or an
     *                  false on an error.
     * @access public
     */
    function factory($log_type, $log_name = '', $ident = '', $conf = array())
    {
        $log_type = strtolower($log_type);
        $classfile = 'Log/' . $log_type . '.php';
        if (@include_once $classfile) {
            $class = 'Log_' . $log_type;
            return new $class($log_name, $ident, $conf);
        } else {
            return false;
        }
    }

    /**
     * Attempts to return a reference to a concrete Log instance of
     * $log_type, only creating a new instance if no log instance with
     * the same parameters currently exists.
     *
     * You should use this if there are multiple places you might
     * create a logger, you don't want to create multiple loggers, and
     * you don't want to check for the existance of one each time. The
     * singleton pattern does all the checking work for you.
     *
     * <b>You MUST call this method with the $var = &Log::singleton()
     * syntax. Without the ampersand (&) in front of the method name,
     * you will not get a reference, you will get a copy.</b>
     * 
     * @param string $log_type  The type of concrete Log subclass to return.
     *                  Attempt to dynamically include the code for
     *                  this subclass. Currently, valid values are
     *                  'syslog', 'sql', 'file', and 'mcal'.
     *
     * @param string $log_name  The name of the actually log file,
     *                  table, or other specific store to use.  Defaults
     *                  to an empty string, with which the subclass will
     *                  attempt to do something intelligent.
     *
     * @param string $ident The identity reported to the log system.
     *
     * @param array $conf   A hash containing any additional
     *                  configuration information that a subclass might need.
     * 
     * @return object Log   The concrete Log reference, or false on an error.
     * @access public
     */
    function &singleton($log_type, $log_name = '', $ident = '', $conf = array())
    {
        static $instances;
        if (!isset($instances)) $instances = array();
        
        $signature = md5($log_type . '][' . $log_name . '][' . $ident . '][' . implode('][', $conf));
        if (!isset($instances[$signature])) {
            $instances[$signature] = Log::factory($log_type, $log_name, $ident, $conf);
        }
        return $instances[$signature];
    }

    /**
     * Returns the string representation of a LOG_* integer constant.
     *
     * @param int $priority The LOG_* integer constant.
     *
     * @return          The string representation of $priority.
     */
    function priorityToString($priority)
    {
        $priorities = array(
            LOG_EMERG   => 'emergency',
            LOG_ALERT   => 'alert',
            LOG_CRIT    => 'critical',
            LOG_ERR     => 'error',
            LOG_WARNING => 'warning',
            LOG_NOTICE  => 'notice',
            LOG_INFO    => 'info',
            LOG_DEBUG   => 'debug'
        );
        return $priorities[$priority];
    }

    /**
     * Adds a Log_observer instance to the list of observers that are
     * be notified when a message is logged.
     *  
     * @param object Log_observer &$logObserver  The Log_observer instance to be added to
     *                      the $listeners array.
     * @access public
     */
    function attach(&$logObserver)
    {
        if (!is_object($logObserver))
            return false;
        
        $logObserver->_listenerID = uniqid(rand());
        
        $this->listeners[$logObserver->_listenerID] = &$logObserver;
    }

    /**
     * Removes a Log_observer instance from the list of observers.
     *
     * @param object Log_observer $logObserver  The Log_observer instance to be removed
     *                      from the $listeners array.
     * @access public
     */
    function detach($logObserver)
    {
        if (isset($this->listeners[$logObserver->_listenerID]))
            unset($this->listeners[$logObserver->_listenerID]);
    }

    /**
     * Sends any Log_observer objects listening to this Log the message
     * that was just logged.
     *
     * @param array $messageOb    The data structure holding all relevant log
     *                      information - the message, the priority, what
     *                      log this is, etc.
     */
    function notifyAll($messageOb)
    {
        reset($this->listeners);
        foreach ($this->listeners as $listener) {
            if ($messageOb['priority'] <= $listener->priority)
                $listener->notify($messageOb);
        }
    }

    /**
     * @return boolean true if this is a composite class, false
     * otherwise. The composite subclass overrides this to return
     * true.
     */
    function isComposite()
    {
        return false;
    }
}

?>
