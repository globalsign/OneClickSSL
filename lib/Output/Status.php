<?php
/**
 * GlobalSign OneclickSSL
 *
 * Replacing the slow and error prone process of CSR creation, key management,
 * approver emails and Certificate installation with a single click!
 *
 * PHP version 5
 *
 * LICENSE: BSD License
 *
 * Copyright © 2012 GMO GlobalsSign KK.
 * All Rights Reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 3. The name of the author may not be used to endorse or promote products
 *    derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY GMO GLOBALSIGN KK "AS IS" AND ANY EXPRESS OR
 * IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
 * OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
 * NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF
 * THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @copyright  Copyright © 2012 GMO GlobalsSign KK. All Rights Reserved. (http://www.globalsign.com)
 * @license    BSD License (3 Clause)
 * @link       http://www.globalsign.com/ssl/oneclickssl/
 * @link       http://globalsign.github.com/OneClickSSL/
 */

/**
 * StatusWriter - Class for recording statuses and writing them to file
 */
class Output_Status
{
    /**
     * Whether status file should be written
     *
     * @var boolean
     */
    protected $_writeStatus = false;

    /**
     * Status handles and states to output, and other information
     *
     * @var array
     */
    protected $_status = array();

    /**
     * Path to where status file should be written
     *
     * @var string
     */
    protected $_statusPath;

    /**
     * Set write status to on or off (true or false)
     *
     * @param bool $value  Set to true to write status array to file
     *
     * @return StatusWriter
     */
    public function setWriteStatus($value)
    {
        if (is_bool($value)) {
            $this->_writeStatus = $value;
        }
        return $this;
    }

    /**
     * Set the path to where status files should be written
     *
     * @param string $path  Path to directory where files should be written
     *
     * @return StatusWriter
     */
    public function setStatusPath($path)
    {
        if (is_dir($path) && is_writable($path)) {
            $this->_statusPath = $path;
        }

        return $this;
    }

    /**
     * Set a key:value pair in the status array. This will overwrite any existing key.
     *
     * @param string $key    Key to set
     * @param mixed  $value  Value to set
     *
     * @return StatusWriter
     */
    public function write($key, $value)
    {
        $this->_status[$key] = $value;
        return $this;
    }

    /**
     * Appends the value to the key as a string.
     *
     * @param string $key    Key to append string to
     * @param string $value  String to append
     *
     * @return StatusWriter
     */
    public function append($key, $value)
    {
        if (!array_key_exists($key, $this->_status)) {
            $this->_status[$key] = '';
        }
        $this->_status[$key] .= $value;
        return $this;
    }

    /**
     * Write the current contents of the status file to disk
     *
     * @param string $domain  Domain to update status for
     *
     * @return boolean
     */
    public function updateStatus($domain)
    {
        $result = false;
        if ($this->_writeStatus && !is_null($this->_statusPath)) {
            // When was the last update of this status file?
            $this->_status['timestamp'] = microtime(true);
            $this->_status['lastupdate'] = date('r');

            // If json_encode doesn't exist we try to build it ourselves (pre-PHP5.2)
            $status = $this->hasJson() ? json_encode($this->_status) : $this->buildJson($this->_status);

            // Write status to disk, prepend domain, we could have more requests at the same time
            $file = $this->_statusPath . DIRECTORY_SEPARATOR . $domain . '_status.json';
            if ((file_exists($file) && is_writable($file)) || (!file_exists($file))) {
               file_put_contents($file, $status);
            } else {
                throw new RunTimeException("Error writing status update to '{$file}'");
            }

            $result = true;
        }
        return $result;
    }

    /**
     * Build the JSON ourselves, in case we're in a version of PHP too young to support json_encode
     *
     * @return string
     */
    protected function buildJson($statusArray)
    {
        $status = '';
        foreach($statusArray as $key => $value) {
            if(strlen($status) > 0) {
                $status .= ',';
            }
            $status .= '"' . addslashes($key) . '":"' . addslashes(str_replace(PHP_EOL, '', nl2br($value))) .'"';
        }
        return "{{$status}}";
    }

    /**
     * Check if json_encode is available (implemented for unit testing)
     *
     * @return boolean
     */
    protected function hasJson()
    {
        return function_exists('json_encode');
    }
}
