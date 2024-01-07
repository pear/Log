<?php
/**
 * $Header$
 * $Horde: horde/lib/Log/sql.php,v 1.12 2000/08/16 20:27:34 chuck Exp $
 *
 * @version $Revision$
 * @package Log
 */

/**
 * We require the PEAR DB class.  This is generally defined in the DB.php file,
 * but it's possible that the caller may have provided the DB class, or a
 * compatible wrapper (such as the one shipped with MDB2), so we first check
 * for an existing 'DB' class before including 'DB.php'.
 */
if (!class_exists('DB')) {
    require_once 'DB.php';
}

/**
 * The Log_sql class is a concrete implementation of the Log::
 * abstract class which sends messages to an SQL server.  Each entry
 * occupies a separate row in the database.
 *
 * This implementation uses PHP's PEAR database abstraction layer.
 *
 * CREATE TABLE log_table (
 *  id          INT NOT NULL,
 *  logtime     TIMESTAMP NOT NULL,
 *  ident       CHAR(16) NOT NULL,
 *  priority    INT NOT NULL,
 *  message     VARCHAR(200),
 *  PRIMARY KEY (id)
 * );
 *
 * @author  Jon Parise <jon@php.net>
 * @since   Horde 1.3
 * @since   Log 1.0
 * @package Log
 *
 * @example sql.php     Using the SQL handler.
 */
class Log_sql extends Log
{
    /**
     * Variable containing the DSN information.
     * @var mixed
     */
    private $dsn = '';

    /**
     * String containing the SQL insertion statement.
     *
     * @var string
     */
    private $sql = '';

    /**
     * Array containing our set of DB configuration options.
     * @var array
     */
    private $options = array('persistent' => true);

    /**
     * Object holding the database handle.
     * @var object
     */
    private $db = null;

    /**
     * Resource holding the prepared statement handle.
     * @var resource
     */
    private $statement = null;

    /**
     * Flag indicating that we're using an existing database connection.
     * @var boolean
     */
    private $existingConnection = false;

    /**
     * String holding the database table to use.
     * @var string
     */
    private $table = 'log_table';

    /**
     * String holding the name of the ID sequence.
     * @var string
     */
    private $sequence = 'log_id';

    /**
     * Maximum length of the $ident string.  This corresponds to the size of
     * the 'ident' column in the SQL table.
     * @var integer
     */
    private $identLimit = 16;

    /**
     * Constructs a new sql logging object.
     *
     * @param string $name         The target SQL table.
     * @param string $ident        The identification field.
     * @param array $conf          The connection configuration array.
     * @param int $level           Log messages up to and including this level.
     */
    public function __construct($name, $ident = '', $conf = array(),
                                $level = PEAR_LOG_DEBUG)
    {
        $this->id = md5(microtime().rand());
        $this->table = $name;
        $this->mask = Log::MAX($level);

        /* Now that we have a table name, assign our SQL statement. */
        if (!empty($conf['sql'])) {
            $this->sql = $conf['sql'];
        } else {
            $this->sql = 'INSERT INTO ' . $this->table .
                          ' (id, logtime, ident, priority, message)' .
                          ' VALUES(?, CURRENT_TIMESTAMP, ?, ?, ?)';
        }

        /* If an options array was provided, use it. */
        if (isset($conf['options']) && is_array($conf['options'])) {
            $this->options = $conf['options'];
        }

        /* If a specific sequence name was provided, use it. */
        if (!empty($conf['sequence'])) {
            $this->sequence = $conf['sequence'];
        }

        /* If a specific sequence name was provided, use it. */
        if (isset($conf['identLimit'])) {
            $this->identLimit = $conf['identLimit'];
        }

        /* Now that the ident limit is confirmed, set the ident string. */
        $this->setIdent($ident);

        /* If an existing database connection was provided, use it. */
        if (isset($conf['db'])) {
            $this->db = &$conf['db'];
            $this->existingConnection = true;
            $this->opened = true;
        } else {
            $this->dsn = $conf['dsn'];
        }
    }

    /**
     * Opens a connection to the database, if it has not already
     * been opened. This is implicitly called by log(), if necessary.
     *
     * @return boolean   True on success, false on failure.
     */
    public function open()
    {
        if (!$this->opened) {
            /* Use the DSN and options to create a database connection. */
            $this->db = &DB::connect($this->dsn, $this->options);
            if (DB::isError($this->db)) {
                return false;
            }

            /* Create a prepared statement for repeated use in log(). */
            if (!$this->prepareStatement()) {
                return false;
            }

            /* We now consider out connection open. */
            $this->opened = true;
        }

        return $this->opened;
    }

    /**
     * Closes the connection to the database if it is still open and we were
     * the ones that opened it.  It is the caller's responsible to close an
     * existing connection that was passed to us via $conf['db'].
     *
     * @return boolean   True on success, false on failure.
     */
    public function close()
    {
        if ($this->opened && !$this->existingConnection) {
            $this->opened = false;
            $this->db->freePrepared($this->statement);
            return $this->db->disconnect();
        }

        return ($this->opened === false);
    }

    /**
     * Sets this Log instance's identification string.  Note that this
     * SQL-specific implementation will limit the length of the $ident string
     * to sixteen (16) characters.
     *
     * @param string    $ident      The new identification string.
     *
     * @since   Log 1.8.5
     */
    public function setIdent($ident)
    {
        $this->ident = substr($ident, 0, $this->identLimit);
    }

    /**
     * Inserts $message to the currently open database.  Calls open(),
     * if necessary.  Also passes the message along to any Log_observer
     * instances that are observing this Log.
     *
     * @param mixed  $message  String or object containing the message to log.
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

        /* If the connection isn't open and can't be opened, return failure. */
        if (!$this->opened && !$this->open()) {
            return false;
        }

        /* If we don't already have our statement object yet, create it. */
        if (!is_object($this->statement) && !$this->prepareStatement()) {
            return false;
        }

        /* Extract the string representation of the message. */
        $message = $this->extractMessage($message);

        /* Build our set of values for this log entry. */
        $id = $this->db->nextId($this->sequence);
        $values = array($id, $this->ident, $priority, $message);

        /* Execute the SQL query for this log entry insertion. */
        $result =& $this->db->execute($this->statement, $values);
        if (DB::isError($result)) {
            return false;
        }

        $this->announce(array('priority' => $priority, 'message' => $message));

        return true;
    }

    /**
     * Prepare the SQL insertion statement.
     *
     * @return boolean  True if the statement was successfully created.
     *
     * @since   Log 1.9.1
     */
    private function prepareStatement()
    {
        $this->statement = $this->db->prepare($this->sql);

        /* Return success if we didn't generate an error. */
        return (DB::isError($this->statement) === false);
    }
}
