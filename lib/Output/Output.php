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
 * Output_Output
 */
class Output_Output
{
    /**
     * debug
     *
     * @var mixed
     */
    protected $_debug;

    /**
     * status
     *
     * @var string
     */
    protected $_status;

    /**
     * __construct
     *
     * @param Output_Debug $debug
     * @param Output_Status $status
     */
    public function __construct(Output_Debug $debug, Output_Status $status)
    {
        // Not sure this is the right place to do this, but it's the most convenient
        // right now.
        $debug->setStatus($status);

        $this->_debug  = $debug;
        $this->_status = $status;
    }

    /**
     * debug
     *
     * @return null
     */
    public function debug()
    {
        return $this->_debug;
    }

    /**
     * status
     *
     * @return null
     */
    public function status()
    {
        return $this->_status;
    }
}
