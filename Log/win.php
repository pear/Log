<?php
// $Id$

/**
 * The Log_win class is a concrete implementation of the Log abstract
 * class that logs messages to a separate browser window.
 *
 * The concept for this log handler is based on part by Craig Davis' article
 * entitled "JavaScript Power PHP Debugging:
 *
 *  http://www.zend.com/zend/tut/tutorial-DebugLib.php
 * 
 * @author  Jon Parise <jon@php.net>
 * @version $Revision$
 * @package Log
 */
class Log_win extends Log
{
    /**
     * The name of the output window.
     * @var string
     * @access private
     */
    var $_name = 'LogWindow';

    /**
     * The title of the output window.
     * @var string
     * @access private
     */
    var $_title = 'Log Output Window';

    /**
     * Mapping of log priorities to colors.
     * @var array
     * @access private
     */
    var $_colors = array(
                        PEAR_LOG_EMERG   => 'red',
                        PEAR_LOG_ALERT   => 'orange',
                        PEAR_LOG_CRIT    => 'yellow',
                        PEAR_LOG_ERR     => 'green',
                        PEAR_LOG_WARNING => 'blue',
                        PEAR_LOG_NOTICE  => 'indigo',
                        PEAR_LOG_INFO    => 'violet',
                        PEAR_LOG_DEBUG   => 'black'
                    );

    /**
     * Constructs a new Log_win object.
     * 
     * @param string $name     Ignored.
     * @param string $ident    The identity string.
     * @param array  $conf     The configuration array.
     * @param array  $maxLevel Maximum priority level at which to log.
     * @access public
     */
    function Log_win($name, $ident = '', $conf = array(),
                          $maxLevel = PEAR_LOG_DEBUG)
    {
        $this->_id = md5(microtime());
        $this->_name = $name;
        $this->_ident = $ident;
        $this->_mask = Log::UPTO($maxLevel);

        if (isset($conf['title'])) {
            $this->_title = $conf['title'];
        }
        if (isset($conf['colors']) && is_array($conf['colors'])) {
            $this->_colors = $conf['colors'];
        }
    }

    /**
     * The first time open() is called, it will open a new browser window and
     * prepare it for output.
     *
     * This is implicitly called by log(), if necessary.
     *
     * @access public
     */
    function open()
    {
        if (!$this->_opened) {
?>
            <script language="JavaScript">
            win = window.open('', '<?php echo $this->_name; ?>', 'toolbar=no,scrollbars,width=600,height=400');
            win.document.writeln('<html>');
            win.document.writeln('<head>');
            win.document.writeln('<title><?php echo $this->_title; ?></title>');
            win.document.writeln('</head>');
            win.document.writeln('<body><pre style="font-size: 8pt">');
            </script>
<?php
            $this->_opened = true;
        }
    }

    /**
     * Closes the connection to the system logger, if it is open.
     *
     * @access public
     */
    function close()
    {
        if ($this->_opened) {
            $this->_writeln('<br /><b>-- End --</b>');
            $this->_opened = false;
        }
    }

    /**
     * Writes a single line of text to the output window.
     *
     * @param string    $line   The line of text to write.
     *
     * @access private
     */
    function _writeln($line)
    {
        echo "<script language='JavaScript'>\n";
        echo "win.document.writeln('$line');\n";
        echo "self.focus();\n";
        echo "</script>\n";
    }

    /**
     * Logs $message to the output window.  The message is also passed along
     * to any Log_observer instances that are observing this Log.
     * 
     * @param string $message  The textual message to be logged.
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

        if (!$this->_opened) {
            $this->open();
        }

        list($usec, $sec) = explode(' ', microtime());

        $line = sprintf('<span style="color: %s">%s.%s [%s] %s</span>',
                        $this->_colors[$priority], strftime('%T', $sec),
                        substr($usec, 2, 2), $this->_ident, nl2br($message));

        $this->_writeln($line);

        $this->_announce(array('priority' => $priority, 'message' => $message));

        return true;
    }
}

?>
