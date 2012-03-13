<?php
/**
* GlobalSign OneclickSSL extention for Example
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
* @copyright  Copyright � 2012 GMO GlobalsSign KK. All Rights Reserved. (http://www.globalsign.com)
* @license    BSD License (3 Clause)
* @version    $Id$
* @link       http://www.globalsign.com/ssl/oneclickssl/
*/

// Please request your own platform id at a GlobalSign representive
define("PLATFORMID", "000000000000000");
define("KEYALGORITHM", "RSA");

require("../../lib/OneClickSSL.php");

class ExampleOneClick implements OneClickSSLPlugin
{
    protected $_output;

    protected $_domain;

    /**
     * Install the certificate
     *
     * @param string    $privateKey     Private key
     * @param string    $certificate    Certificate
     * @param string    $cacert         CA Intermediates
     * 
     * @return string
     */
    public function install($privateKey, $certificate, $cacert = null)
    {  	    			
	// Just some debugging information
	$this->debug(1, "Exporting certificates to the file system");
  	$this->debug(2, "Certificate:". PHP_EOL . $certificate);
	$this->debug(2, "Intermediates:". PHP_EOL . $cacert);
	
	// You need to save the certificates to you filesystem, database or any
	// other location where your webserver, proxy or loadbalancer can use them.
	
	// Return certificate
        return $certificate;
    }
    
    /**
     * Check IP requirements, assign a unique IP if availible
     *
     * @return bool
     */
    public function checkIp()
    {
        // Return true on success, remove the function if you don't
        // want check and assign ip addresses
        return false;
    }
	
    /**
     * Back the current certificates
     *
     * @return bool
     */     
    public function backup()
    {
        // Return true on success, remove the function if you don't
        // want to create a backup
        return false;
    }
	
    /**
     * Restore the backup certificates
     *
     * @return bool
     */     
    public function restoreBackup()
    {
        // Return true on success, remove the function if you don't
        // want to restore a backup
        return false;
    }
    
    /**
     * Set the domain for the certificate
     *
     * @param string $domain  The domain for the certificate
     *
     * @return null
     */
    public function setDomain($domain)
    {
        $this->_domain = $domain;
    }
    
    /**
     * Set the output handler object
     *
     * @param Output_Output $output  Output handler object
     *
     * @return ExampleOneClick
     */
    public function setOutput(Output_Output $output)
    {
        $this->_output = $output;
        return $this;
    }
    
    /**
     * Write a message to the debugger
     *
     * @param int    $level    Level of message to send to debug
     * @param string $message  Message to send
     *
     * @return null
     */
    protected function debug($level, $message)
    {
        $this->_output->debug()->write($level, $message);
    }
}


/**
 * Initiate OneClickSSL Procedure
 *  $domain, $email, $voucher, $port = self::DEFAULT_SSL_PORT, $lang = self::DEFAULT_LANG 
 */
$certData = new CertificateData('www.example.com',
                                'my.mail@example.com',
                                '5daytrialDV');
  
$oneclick = OneClickSSL::init($certData, new NginxOneClick());

// How much output do you want?
$oneclick->output()->debug()->setLevel(1);

// Run on production (0), testing (1) or staging server (2)
//$oneclick->setEnvironment(1);

// Write procgress into status file (default: 0)
//$oneclick->output()->status()->setStatusPath(realpath('/tmp/'))->setWriteStatus(true);

$oneclick->order();
