<?php
/**
 * $Header$
 *
 * @version $Revision$
 * @package Log
 */

/**
 * The Log_console class is a concrete implementation of the Log::
 * abstract class which writes message to the text console.
 *
 * @author  Jon Parise <jon@php.net>
 * @since   Log 1.1
 * @package Log
 *
 * @example console.php     Using the console handler.
 */
class Log_console extends Log
{
    /**
     * Handle to the current output stream.
     * @var resource
     */
    private $stream = null;

    /**
     * Is this object responsible for closing the stream resource?
     * @var bool
     */
    private $closeResource = false;

    /**
     * Should the output be buffered or displayed immediately?
     * @var string
     */
    private $buffering = false;

    /**
     * String holding the buffered output.
     * @var string
     */
    private $buffer = '';

    /**
     * String containing the format of a log line.
     * @var string
     */
    private $lineFormat = '%1$s %2$s [%3$s] %4$s';

    /**
     * String containing the timestamp format.  It will be passed directly to
     * strftime().  Note that the timestamp string will generated using the
     * current locale.
     * @var string
     */
    private $timeFormat = '%b %d %H:%M:%S';

    /**
     * Constructs a new Log_console object.
     *
     * @param string $name     Ignored.
     * @param string $ident    The identity string.
     * @param array  $conf     The configuration array.
     * @param int    $level    Log messages up to and including this level.
     */
    public function __construct($name, $ident = '', $conf = array(),
                                $level = PEAR_LOG_DEBUG)
    {
        $this->id = md5(microtime().rand());
        $this->ident = $ident;
        $this->mask = Log::MAX($level);

        if (!empty($conf['stream'])) {
            $this->stream = $conf['stream'];
        } elseif (defined('STDOUT')) {
            $this->stream = STDOUT;
        } else {
            $this->stream = fopen('php://output', 'a');
            $this->closeResource = true;
        }

        if (isset($conf['buffering'])) {
            $this->buffering = $conf['buffering'];
        }

        if (!empty($conf['lineFormat'])) {
            $this->lineFormat = str_replace(array_keys($this->formatMap),
                                             array_values($this->formatMap),
                                             $conf['lineFormat']);
        }

        if (!empty($conf['timeFormat'])) {
            $this->timeFormat = $conf['timeFormat'];
        }

        /*
         * If output buffering has been requested, we need to register a
         * shutdown function that will dump the buffer upon termination.
         */
        if ($this->buffering) {
            register_shutdown_function(array(&$this, 'log_console_destructor'));
        }
    }

    /**
     * Destructor
     */
    private function log_console_destructor()
    {
        $this->close();
    }

    /**
     * Open the output stream.
     *
     * @since Log 1.9.7
     */
    public function open()
    {
        $this->opened = true;
        return true;
    }

    /**
     * Closes the output stream.
     *
     * This results in a call to flush().
     *
     * @since Log 1.9.0
     */
    public function close()
    {
        $this->flush();
        $this->opened = false;
        if ($this->closeResource === true && is_resource($this->stream)) {
            fclose($this->stream);
        }
        return true;
    }

    /**
     * Flushes all pending ("buffered") data to the output stream.
     *
     * @since Log 1.8.2
     */
    public function flush()
    {
        /*
         * If output buffering is enabled, dump the contents of the buffer to
         * the output stream.
         */
        if ($this->buffering && (strlen($this->buffer) > 0)) {
            fwrite($this->stream, $this->buffer);
            $this->buffer = '';
        }

        if (is_resource($this->stream)) {
            return fflush($this->stream);
        }

        return false;
    }

    /**
     * Writes $message to the text console. Also, passes the message
     * along to any Log_observer instances that are observing this Log.
     *
     * @param mixed  $message    String or object containing the message to log.
     * @param string $priority The priority of the message.  Valid
     *                  values are: PEAR_LOG_EMERG, PEAR_LOG_ALERT,
     *                  PEAR_LOG_CRIT, PEAR_LOG_ERR, PEAR_LOG_WARNING,
     *                  PEAR_LOG_NOTICE, PEAR_LOG_INFO, and PEAR_LOG_DEBUG.
     * @return boolean  True on success or false on failure.
     */
    public function log($message, $priority = null)
    {
        /* If a priority hasn't been specified, use the default value. */
        if ($priority === null) {
            $priority = $this->priority;
        }

        /* Abort early if the priority is above the maximum logging level. */
        if (!$this->isMasked($priority)) {
            return false;
        }

        /* Extract the string representation of the message. */
        $message = $this->extractMessage($message);

        /* Build the string containing the complete log line. */
        $line = $this->format($this->lineFormat,
                               strftime($this->timeFormat),
                               $priority, $message) . "\n";

        /*
         * If buffering is enabled, append this line to the output buffer.
         * Otherwise, print the line to the output stream immediately.
         */
        if ($this->buffering) {
            $this->buffer .= $line;
        } else {
            fwrite($this->stream, $line);
        }

        /* Notify observers about this log message. */
        $this->announce(array('priority' => $priority, 'message' => $message));

        return true;
    }
}
