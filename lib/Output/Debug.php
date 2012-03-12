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
 * @version    $Id$
 * @link       http://www.globalsign.com/ssl/oneclickssl/
 * @link       http://globalsign.github.com/OneClickSSL/
 */

/**
 * Output_Debug
 */
class Output_Debug
{
    /**
     * File handle resource to write output to
     *
     * @var resource
     */
    protected $_fh;

    /**
     * Debugging level. Only output if this value is higher than the level passed in
     * with each message
     *
     * @var int
     */
    protected $_level = 0;

    /**
     * Status Writer object, for outputting status information.
     *
     * @var Output_Status
     */
    protected $_status;

    /**
     * Set up the DebugLogger
     *
     * @param resource $writeTo  Alternative file handle to write output to
     */
    public function __construct($writeTo = null)
    {
        $this->_fh = is_resource($writeTo) ? $writeTo : fopen('php://output', 'w');
    }

    /**
     * Set the Status Writer object
     *
     * @param StatusWriter $statusWriter Object to set
     *
     * @return DebugLogger
     */
    public function setStatus(Output_Status $status)
    {
        $this->_status = $status;
        return $this;
    }

    /**
     * Set the debugging level. If the value passed in isn't a numeric the value won't
     * be changed, but program execution will otherwise continue.
     *
     * @param int $level  Debugging level to set
     *
     * @return DebugLogger
     */
    public function setLevel($level)
    {
        if (is_numeric($level)) {
            $this->_level = $level;
        }
        return $this;
    }

    /**
     * Output a debug message, if the level is lower or equal to the current debugging level
     *
     * @param int    $level  Output message if debugging level is higher than this value
     * @param string $msg    Message to output
     *
     * @return DebugLogger
     */
    public function write($level, $msg)
    {
        if ($this->_level >= $level && strlen(trim($msg)) > 0) {
            fwrite($this->_fh, $msg . PHP_EOL);

            if ($this->_status instanceOf Output_Status) {
                // Write debug messages to Status file as well
                $this->_status->append('debug', $msg . PHP_EOL);
            }
        }
        return $this;
    }
}
