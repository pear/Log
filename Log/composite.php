<?php
// $Id$
// $Horde: horde/lib/Log/composite.php,v 1.2 2000/06/28 21:36:13 jon Exp $

/**
 * The Log_composite:: class implements a Composite pattern which
 * allows multiple Log implementations to get sent the same events.
 *
 * @author  Chuck Hagenbuch <chuck@horde.org>
 * @version $Revision$
 * @since Horde 1.3
 * @package Log 
 */

class Log_composite extends Log {

    /** 
    * Array holding all Log instances 
    * which should be sent events sent to the composite. 
    * @var array
    */
    var $children = array();


    /**
     * Constructs a new composite Log object.
     * 
     * @param boolean $name     This is ignored.
     * @param boolean $ident    This is ignored.
     * @param boolean $conf     This is ignored.
     * @param boolean $maxLevel This is ignored.
     * @access public
     */
    function Log_composite($name = false, $ident = false, $conf = false,
                           $maxLevel = LOG_DEBUG)
    {
    }
    
    /**
     * Open the log connections of each and every child of this
     * composite.
     * @access public     
     */
    function open()
    {
        if (!$this->_opened) {
            reset($this->children);
            foreach ($this->children as $child) {
                $child->open();
            }
        }
    }

    /**
     * If we've gone ahead and opened each child, go through and close
     * each child.
     * @access public     
     */
    function close()
    {
        if ($this->_opened) {
            reset($this->children);
            foreach ($this->children as $child) {
                $child->close();
            }
        }
    }

    /**
     * Sends $message and $priority to every child of this composite.
     * 
     * @param string $message  The textual message to be logged.
     * @param string $priority (optional) The priority of the message. Valid
     *                  values are: LOG_EMERG, LOG_ALERT, LOG_CRIT,
     *                  LOG_ERR, LOG_WARNING, LOG_NOTICE, LOG_INFO,
     *                  and LOG_DEBUG. The default is LOG_INFO.
     */
    function log($message, $priority = LOG_INFO)
    {
        reset($this->children);
        foreach ($this->children as $child) {
            $child->log($message, $priority);
        }
        
        $this->notifyAll(array('priority' => $priority, 'message' => $message));
    }

    /**
     * @return boolean true if this is a composite class, false
     * otherwise. Always returns true since this is the composite
     * subclass.
     * @access public
     */
    function isComposite()
    {
        return true;
    }

    /**
     * Add a Log instance to the list of children that messages sent
     * to us should be passed on to.
     *
     * @param object Log &$child The Log instance to add.
     * @access public 
     * @return boolean false, if &$child isn't a Log instance    
     */
    function addChild(&$child)
    {
        if (!is_object($child)) {
            return false;
        }

        $child->_childID = uniqid(rand());

        $this->children[$child->_childID] = &$child;
    }

    /**
     * Remove a Log instance from the list of children that messages
     * sent to us should be passed on to.
     *
     * @param object Log $child The Log instance to remove.
     * @access public     
     */
    function removeChild($child)
    {
        if (isset($this->children[$child->_childID])) {
            unset($this->children[$child->_childID]);
        }
    }
}

?>
