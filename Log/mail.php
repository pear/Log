<?php
// $Id$

/**
 * The Log_mail class is a concrete implementation of the Log::
 * abstract class which sends log messages to a mailbox.
 * The mail is actually sent when you close() the logger.
 * If you do not close() it, the pending log messages will be lost.
 * 
 * @author  Ronnie Garcia <ronnie@mk2.net>
 * @author  Jon Parise <jon@php.net>
 * @version $Revision$
 * @package Log
 */
class Log_mail extends Log {

    /** 
     * String holding the recipient's email address.
     * @var string
     */
    var $_recipient = '';

    /** 
     * String holding the sender's email address.
     * @var string
     */
    var $_from = '';

    /** 
     * String holding the email's subject.
     * @var string
     */
    var $_subject = '[Log_mail] Log message';

    /**
     * String holding the mail message body.
     * @var string
     */
    var $_message = '';


    /**
     * Constructs a new Log_mail object.
     * 
     * Here is how you can customize the mail driver with the conf[] hash :
     *   $conf['from']    : the mail's "From" header line,
     *   $conf['subject'] : the mail's "Subject" line.
     * 
     * @param string $name      The filename of the logfile.
     * @param string $ident     The identity string.
     * @param array  $conf      The configuration array.
     * @param int    $maxLevel  Maximum level at which to log.
     * @access public
     */
    function Log_mail($name, $ident = '', $conf = array(),
                      $maxLevel = PEAR_LOG_DEBUG)
    {
        $this->_recipient = $name;
        $this->_ident    = $ident;
        $this->_maxLevel = $maxLevel;

        if (!empty($conf['from'])) {
            $this->_from = $conf['from'];
        } else {
            $this->_from = ini_get('sendmail_from');
        }
        
        if (!empty($conf['subject'])) {
            $this->_subject = $conf['subject'];
        }
    }

    /**
     * Starts a new mail message.
     * This is implicitly called by log(), if necessary.
     * @access public
     */
    function open()
    {
        if (!$this->_opened) {
            $this->_message = "Log messages:\n\n";
            $this->_opened = true;
        }
    }

    /**
     * Closes the message, if it is open, and sends the mail.
     * @access public
     */
    function close()
    {
        if ($this->_opened) {
            if (!empty($this->_message)) {
                $headers = "From: $this->_from\r\n";
                $headers .= "User-Agent: Log_mail\r\n";

                if (mail($this->_recipient, $this->_subject, $this->_message,
                        $headers) == false) {
                    error_log("Log_mail: Failure executing mail()", 0);
                    return false;
                }
            }
            $this->_opened = false;
        }

        return true;
    }

    /**
     * Writes $message to the currently open mail message. Calls open(), if
     * necessary.
     * We still need to close() the logger to actually send the mail, thought.
     * 
     * @param string $message  The textual message to be logged.
     * @param string $priority The priority of the message.  Valid
     *                  values are: PEAR_LOG_EMERG, PEAR_LOG_ALERT,
     *                  PEAR_LOG_CRIT, PEAR_LOG_ERR, PEAR_LOG_WARNING,
     *                  PEAR_LOG_NOTICE, PEAR_LOG_INFO, and PEAR_LOG_DEBUG.
     *                  The default is PEAR_LOG_INFO.
     * @access public
     */
    function log($message, $priority = PEAR_LOG_INFO)
    {
        /* Abort early if the priority is above the maximum logging level. */
        if ($priority > $this->_maxLevel) return;

        if (!$this->_opened) {
            $this->open();
        }

        $entry = sprintf("%s %s [%s] %s\n", strftime('%b %d %H:%M:%S'),
            $this->_ident, Log::priorityToString($priority), $message);

        $this->_message .= $entry;

        $this->notifyAll(array('priority' => $priority, 'message' => $message));
    }
}

?>
