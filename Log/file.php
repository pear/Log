<?php
// +-----------------------------------------------------------------------+
// | Copyright (c) 2002  Richard Heyes                                     |
// | All rights reserved.                                                  |
// |                                                                       |
// | Redistribution and use in source and binary forms, with or without    |
// | modification, are permitted provided that the following conditions    |
// | are met:                                                              |
// |                                                                       |
// | o Redistributions of source code must retain the above copyright      |
// |   notice, this list of conditions and the following disclaimer.       |
// | o Redistributions in binary form must reproduce the above copyright   |
// |   notice, this list of conditions and the following disclaimer in the |
// |   documentation and/or other materials provided with the distribution.|
// | o The names of the authors may not be used to endorse or promote      |
// |   products derived from this software without specific prior written  |
// |   permission.                                                         |
// |                                                                       |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS   |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT     |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR |
// | A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT  |
// | OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, |
// | SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT      |
// | LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, |
// | DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY |
// | THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT   |
// | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE |
// | OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.  |
// |                                                                       |
// +-----------------------------------------------------------------------+
// | Author: Richard Heyes <richard@phpguru.org>                           |
// |         Jon Parise <jon@php.net>                                      |
// +-----------------------------------------------------------------------+
//
// $Id$

/**
* The Log_file class is a concrete implementation of the Log::
* abstract class which writes message to a text file. This is based
* on the previous Log_file class by Jon Parise.
* 
* @author  Richard Heyes <richard@php.net>
* @version $Revision$
* @package Log
*/
class Log_file extends Log
{
    /** 
    * String holding the filename of the logfile. 
    * @var string
    */
    var $_filename;

    /**
    * No idea what this does.
    * @var string (maybe)
    */
    var $_ident;

    /**
    * Maximum level to log
    * @var integer
    */
    var $_maxLevel;

    /**
    * Integer holding the file handle. 
    * @var integer
    */
    var $_fp;

    /**
    * Integer (in octal) containing the logfile's permissions mode.
    * @var integer
    */
    var $_mode = 0644;

    /**
    * Array holding the lines to log
    * @var array
    */
    var $_logLines;

    /**
    * Boolean which if true will mean
    * the lines are *NOT* written out.
    */
    var $_writeOut;

    /**
    * Creates a new logfile object.
    * 
    * @param  string $name     The filename of the logfile.
    * @param  string $ident    The identity string.
    * @param  array  $conf     The configuration array.
    * @param  int    $maxLevel Maximum level at which to log.
    * @access public
    */
    function Log_File($name, $ident = '', $conf = array(), $maxLevel = PEAR_LOG_DEBUG)
    {
        /* If a file mode has been provided, use it. */
        if (!empty($conf['mode'])) {
            $this->_mode = $conf['mode'];
        }

        if (!file_exists($name)) {
            touch($name);
            chmod($name, $this->_mode);
        }

        $this->_filename = realpath($name);
        $this->_ident    = $ident;
        $this->_maxLevel = $maxLevel;
        
        $this->_logLines = array();
        $this->_writeOut = true;
        
        $this->PEAR();
    }
    
    /**
    * Destructor. This will write out any lines to the logfile, UNLESS the dontLog()
    * method has been called, in which case it won't.
    *
    * @access private
    */
    function _Log_File()
    {
        $this->_PEAR();

        if (!empty($this->_logLines) AND $this->_writeOut AND $this->_openLogfile()) {
            
            foreach ($this->_logLines as $line) {
                $this->_writeLine($line['message'], $line['priority'], $line['time']);
            }

            $this->_closeLogfile();
        }
    }

    /**
    * Adds a line to be logged. Adds it to the internal array and will only
    * get written out when the destructor is called.
    *
    * @param string $message  The textual message to be logged.
    * @param string $priority The priority of the message.  Valid
    *                  values are: PEAR_LOG_EMERG, PEAR_LOG_ALERT, PEAR_LOG_CRIT,
    *                  PEAR_LOG_ERR, PEAR_LOG_WARNING, PEAR_LOG_NOTICE, PEAR_LOG_INFO, and
    *                  PEAR_LOG_DEBUG. The default is PEAR_LOG_INFO.
    * @return boolean  True on success or false on failure.
    * @access public
    */
    function log($message, $priority = PEAR_LOG_INFO)
    {
        // Abort early if the priority is above the maximum logging level.
        if ($priority > $this->_maxLevel) {
            return false;
        }

        // Add to loglines array
        $this->_logLines[] = array('message' => $message, 'priority' => $priority, 'time' => strftime('%b %d %H:%M:%S'));

        // Notify observers
        $this->notifyAll(array('message' => $message, 'priority' => $priority));

        return true;
    }
    
    /**
    * This function will prevent the destructor from logging.
    *
    * @access public
    */
    function dontLog()
    {
        $this->_writeOut = false;
    }

    /**
    * Function to force writing out of log *now*. Will clear the queue.
    * Using this function does not cancel the writeout in the destructor.
    * Handy for long running processes.
    *
    * @access public
    */
    function writeOut()
    {
        if (!empty($this->_logLines) AND $this->_openLogfile()) {
            
            foreach ($this->_logLines as $line) {
                $this->_writeLine($line['message'], $line['priority'], $line['time']);
            }

            $this->_logLines = array();
            $this->_closeLogfile();
        }
    }

    /**
    * Opens the logfile for appending. File should always exist, as
    * constructor will create it if it doesn't.
    *
    * @access private
    */
    function _openLogfile()
    {
        if (($this->_fp = @fopen($this->_filename, 'a')) == false) {
            return false;
        }

        chmod($this->_filename, $this->_mode);

        return true;
    }
    
    /**
    * Closes the logfile file pointer.
    *
    * @access private
    */
    function _closeLogfile()
    {
        return fclose($this->_fp);
    }

    /**
    * Writes a line to the logfile
    *
    * @param  string $line      The line to write
    * @param  integer $priority The priority of this line/msg
    * @return integer           Number of bytes written or -1 on error
    * @access private
    */
    function _writeLine($line, $priority, $time)
    {
        return fwrite($this->_fp, sprintf("%s %s [%s] %s\r\n", $time, $this->_ident, $this->priorityToString($priority), $line));
    }

} // End of class
?>
