<?php
// $Id$
// $Horde: horde/lib/Log/composite.php,v 1.2 2000/06/28 21:36:13 jon Exp $

/**
 * The Log_composite:: class implements a Composite pattern which
 * allows multiple Log implementations to receive the same events.
 *
 * @author  Chuck Hagenbuch <chuck@horde.org>
 * @author  Jon Parise <jon@php.net>
 * @version $Revision$
 * @since Horde 1.3
 * @package Log
 */

class Log_composite extends Log
{
    /**
     * Array holding all of the Log instances to which log events should be
     * sent.
     *
     * @var array
     */
    var $_children = array();


    /**
     * Construct a new composite Log object.
     *
     * @param boolean   $name       This parameter is ignored.
     * @param boolean   $ident      This parameter is ignored.
     * @param boolean   $conf       This parameter is ignored.
     * @param boolean   $maxLevel   This parameter is ignored.
     *
     * @access public
     */
    function Log_composite($name = false, $ident = false, $conf = false,
                           $maxLevel = PEAR_LOG_DEBUG)
    {
    }

    /**
     * Open the child connections.
     *
     * @access public
     */
    function open()
    {
        if (!$this->_opened) {
            foreach ($this->_children as $id => $child) {
                $this->_children[$id]->open();
            }
        }
    }

    /**
     * Close any child instances.
     *
     * @access public
     */
    function close()
    {
        if ($this->_opened) {
            foreach ($this->_children as $id => $child) {
                $this->_children[$id]->close();
            }
        }
    }

    /**
     * Send $message and $priority to each child of this composite.
     *
     * @param string    $message    The textual message to be logged.
     * @param string    $priority   (optional) The priority of the message.
     *                              Valid values are: PEAR_LOG_EMERG,
     *                              PEAR_LOG_ALERT, PEAR_LOG_CRIT,
     *                              PEAR_LOG_ERR, PEAR_LOG_WARNING,
     *                              PEAR_LOG_NOTICE, PEAR_LOG_INFO, and
     *                              PEAR_LOG_DEBUG.
     *                              The default is PEAR_LOG_INFO.
     *
     * @return boolean  True if the entry is successfully logged.
     *
     * @access public
     */
    function log($message, $priority = PEAR_LOG_INFO)
    {
        foreach ($this->_children as $id => $child) {
            $this->_children[$id]->log($message, $priority);
        }

        $this->notifyAll(array('priority' => $priority, 'message' => $message));

        return true;
    }

    /**
     * Return true if this is a composite.
     *
     * @return boolean  True if this is a composite class.
     *
     * @access public
     */
    function isComposite()
    {
        return true;
    }

    /**
     * Add a Log instance to the list of children.
     *
     * @param object    $child      The Log instance to add.
     *
     * @return boolean  True if the Log instance was successfully added.
     *
     * @access public
     */
    function addChild(&$child)
    {
        /* Make sure this is a Log instance. */
        if (!is_a($child, 'Log')) {
            return false;
        }

        $this->_children[$child->_id] = &$child;

        return true;
    }

    /**
     * Remove a Log instance from the list of children.
     *
     * @param object    $child      The Log instance to remove.
     *
     * @return boolean  True if the Log instance was successfully removed.
     *
     * @access public
     */
    function removeChild($child)
    {
        if (!is_a($child, 'Log') || !isset($this->_children[$child->_id])) {
            return false;
        }

        unset($this->_children[$child->_id]);

        return true;
    }
}

?>
