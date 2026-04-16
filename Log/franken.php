<?php

declare(strict_types=1);
/**
 * $Header$
 *
 * @version $Revision$
 * @package Log
 */

/**
 * The Log_Franken class is a concrete implementation of the Log::
 * abstract class which sends messages to the FrankenPHP logger.
 *
 * @author  Karel Kozlik <karek.kozlik@frafos.com>
 * @package Log
 *
 * @example franken.php      Using the frankenphp_log function.
 */
class Log_Franken extends Log
{
    /**
     * Integer holding the log facility to use.
     */
    private int $name;

    /**
     * String containing the format of a message.
     */
    private string $lineFormat = '%4$s';

    /**
     * String containing the timestamp format. It will be passed to date().
     * If timeFormatter configured, it will be used.
     * current locale.
     */
    private string $timeFormat = 'M d H:i:s';

    /**
     * @var callable
     */
    private $timeFormatter;


    /**
     * Constructs a new log object.
     *
     * @param string $name     Ignored.
     * @param string $ident    The identity string.
     * @param array  $conf     The configuration array.
     * @param int    $level    Log messages up to and including this level.
     */
    public function __construct(
        string $name,
        string $ident = '',
        array $conf = [],
        int $level = PEAR_LOG_DEBUG
    ) {

        if (!empty($conf['lineFormat'])) {
            $this->lineFormat = str_replace(array_keys($this->formatMap),
                                            array_values($this->formatMap),
                                            $conf['lineFormat']);
        }
        else {
            if ($ident !== '') {
                $this->lineFormat = '%2$s: ' . $this->lineFormat;
            }
        }

        if (!empty($conf['timeFormat'])) {
            $this->timeFormat = $conf['timeFormat'];
        }

        if (!empty($conf['timeFormatter'])) {
            $this->timeFormatter = $conf['timeFormatter'];
        }

        $this->id = md5(microtime().random_int(0, mt_getrandmax()));
        $this->ident = $ident;
        $this->mask = Log::MAX($level);
    }

    /**
     * Opens the FrankenPHP handler.
     */
    public function open(): bool
    {
        $this->opened = true;

        return $this->opened;
    }

    /**
     * Closes the FrankenPHP handler.
     */
    public function close(): bool
    {
        $this->opened = false;

        return true;
    }

    /**
     * Sends $message to the FrankenPHP logger. Also passes the message
     * along to any Log_observer instances that are observing this Log.
     *
     * @param mixed $message String or object containing the message to log.
     * @param int|null $priority (optional) The priority of the message.  Valid
     *                  values are: PEAR_LOG_EMERG, PEAR_LOG_ALERT,
     *                  PEAR_LOG_CRIT, PEAR_LOG_ERR, PEAR_LOG_WARNING,
     *                  PEAR_LOG_NOTICE, PEAR_LOG_INFO, and PEAR_LOG_DEBUG.
     * @return boolean  True on success or false on failure.
     */
    public function log($message, ?int $priority = null): bool
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

        /* Build a syslog priority value based on our current configuration. */
        $frankenPriority = $this->toFranken($priority);

        /* Apply the configured line format to the message string. */
        $message = $this->format($this->lineFormat,
                                 $this->formatTime(time(), $this->timeFormat, $this->timeFormatter),
                                 $priority, $message);

        frankenphp_log($message, $frankenPriority);

        $this->announce(['priority' => $priority, 'message' => $message]);

        return true;
    }

    /**
     * Converts a PEAR_LOG_* constant into a franken FRANKENPHP_LOG_LEVEL_* constant.
     *
     * @param int $priority     PEAR_LOG_* value to convert to FRANKENPHP_LOG_LEVEL_* value.
     *
     * @return int The FRANKENPHP_LOG_LEVEL_* representation of $priority.
     */
    private function toFranken(int $priority): int
    {
        static $priorities = [
            PEAR_LOG_EMERG   => FRANKENPHP_LOG_LEVEL_ERROR,
            PEAR_LOG_ALERT   => FRANKENPHP_LOG_LEVEL_ERROR,
            PEAR_LOG_CRIT    => FRANKENPHP_LOG_LEVEL_ERROR,
            PEAR_LOG_ERR     => FRANKENPHP_LOG_LEVEL_ERROR,
            PEAR_LOG_WARNING => FRANKENPHP_LOG_LEVEL_WARN,
            PEAR_LOG_NOTICE  => FRANKENPHP_LOG_LEVEL_INFO,
            PEAR_LOG_INFO    => FRANKENPHP_LOG_LEVEL_INFO,
            PEAR_LOG_DEBUG   => FRANKENPHP_LOG_LEVEL_DEBUG,
        ];

        /* If we're passed an unknown priority, default to FRANKENPHP_LOG_LEVEL_INFO. */
        if (!array_key_exists($priority, $priorities)) {
            echo "Unknown priority level: $priority. Defaulting to FRANKENPHP_LOG_LEVEL_INFO.\n";
            var_dump($priority);
            var_dump($priorities);
            return FRANKENPHP_LOG_LEVEL_INFO;
        }

        return $priorities[$priority];
    }
}
