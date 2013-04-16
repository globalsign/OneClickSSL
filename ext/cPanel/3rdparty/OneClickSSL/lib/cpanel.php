<?php
/**
* GlobalSign OneclickSSL extention for DirectAdmin
*
* Replacing the slow and error prone process of CSR creation, key management,
* approver emails and Certificate installation with a single click!
*
* PHP version 5
*
* LICENSE: BSD License
*
* Copyright � 2012 GMO GlobalsSign KK.
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

/*
 * Crease cPnanel Class
 */
$cpanel = &new CPANEL();
global $cpanel;

define("PLATFORMID", "642889112500002");
define("KEYALGORITHM", "RSA");

require 'OneClickSSL.php';

class DAOneClick implements OneClickSSLPlugin
{
    protected $_output;

    protected $_domain;

    public $backup = array();

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
     * Install the certificate
     */
    public function install($privateKey, $certificate, $cacert = null)
    {
		global $cpanel;
		
        $this->debug(1, "Preparing cPanel certificate installation for ". $this->_domain);

        // Just some debugging information
        $this->debug(2, "Certificate:\n". $certificate);
        $this->debug(2, "Intermediates:\n". $cacert);

		$this->updateStatus();
		
        // Export the certificates to the filesystem
        openssl_pkey_export($privateKey,$privkeySting);
        openssl_x509_export($certificate,$certString);
        if (strlen($cacert) > 10) {
            openssl_x509_export($cacert,$cacertString);
        }

		// Parameters for SSL Certificate installation
		// http://docs.cpanel.net/twiki/bin/view/ApiDocs/Api2/ApiSSL#SSL::installssl
		$params = array();
		$params['domain'] = $this->_domain;
		$params['key'] = $privkeySting;
		$params['crt'] = $certString;
		$params['cabundle'] = $cacertString;
		
		$res = $cpanel->api2('SSL', 'installssl', $params);
		if ($res['cpanelresult']['data'][0]['result']) {
			$this->debug(1, "Installation of certificate and key material completed");
		} else {
			$this->debug(1, "Installation of certificate and key material failed");
			$this->debug(2, strip_tags($res['cpanelresult']['data'][0]['output']));
			$this->debug(3, print_r($res, true));
			$this->updateStatus();
			return false;
		}
		$this->updateStatus();
		
        return $certString;
    }

    /**
     * Check IP requirements, assign a unique IP if availible
     */
/*    public function checkIp() {
        global $usrSettings, $settings;
        
		$this->debug(1, "Check IP requirements");
		$this->updateStatus();
		
        // Skip if we want to use Server Name Indication
    	if ((ctype_digit($usrSettings['sni']) && $usrSettings['sni'] > 0)
    		|| (ctype_digit($settings['sni']) && $settings['sni'] > 0)
    	) {
    		$this->debug(1, "Using Server Name Indication (SNI), skipping IP checks.");
            return true;
        }
        
		return true;
    }
*/
    /**
     * Back the current certificates
     */
    public function backup()
    {
		global $cpanel;
		
		// Using three calls to API1 as we get an error in API2:
		//   "Could not find function 'fetchinfo' in module 'SSL'"
		// API1: http://docs.cpanel.net/twiki/bin/view/ApiDocs/Api1/ApiSSL#SSL::showcrt
		// API2: http://docs.cpanel.net/twiki/bin/view/ApiDocs/Api2/ApiSSL#SSL::fetchinfo
		$params = array();
		$params[$this->_domain] = false;
		$res = $cpanel->api1('SSL', 'showkey', $params);
		if ($res['cpanelresult']['event']['result']) {
			$this->debug(1, "Backup of private key completed");
			$this->backup['key'] = $res['cpanelresult']['data']['result'];
			
		} else {
			$this->debug(1, "Backup of private key failed");
			$this->debug(3, strip_tags(print_r($res, true)));
			return false;
		}
		
		// backup cert
		$res = $cpanel->api1('SSL', 'showcrt', $params);
		if ($res['cpanelresult']['event']['result']) {
			$this->debug(1, "Backup of certificate completed");
			$this->backup['crt'] = $res['cpanelresult']['data']['result'];
			
		} else {
			$this->debug(1, "Backup of certificate failed");
			$this->debug(3, strip_tags(print_r($res, true)));
			return false;
		}
		
		// backup cabundle
		$params = array();
		$params[$this->_domain] = $this->backup['crt'];
		$res = $cpanel->api1('SSL', 'getcabundle', $params);
		if ($res['cpanelresult']['event']['result']) {
			$this->debug(1, "Backup of intermediate certificate(s) completed");
			// result is html formatted, extract pem data
			preg_match("/(-+BEGIN .* CERTIFICATE-+)/", strip_tags($res['cpanelresult']['data']['result']), $tmp);
			$this->backup['cab'] = str_replace('\n', PHP_EOL, $tmp[1]);
			
		} else {
			$this->debug(1, "Backup of intermediate certificate(s) failed");
			$this->debug(3, strip_tags(print_r($res, true)));
			return false;
		}
		
		$this->updateStatus();
		
        return true;
    }

    /**
     * Restore the backup certificates
     */
    public function restoreBackup()
    {
		global $cpanel;
		
		// Parameters for SSL Certificate installation
		// http://docs.cpanel.net/twiki/bin/view/ApiDocs/Api2/ApiSSL#SSL::installssl
		$params = array();
		$params['domain'] = $this->_domain;
		$params['key'] = $this->backup['key'];
		$params['crt'] = $this->backup['crt'];
		$params['cabundle'] = $this->backup['cab'];
		
		$res = $cpanel->api2('SSL', 'installssl', $params);
		if ($res['cpanelresult']['data'][0]['result']) {
			$this->debug(1, "Restore of certificate and key material completed");
		} else {
			$this->debug(1, "Restore of certificate and key material failed");
			$this->debug(2, strip_tags($res['cpanelresult']['data'][0]['output']));
			$this->debug(3, "Private key: ". PHP_EOL . $this->backup['key']);
			$this->debug(3, "Certificate: ". PHP_EOL . $this->backup['crt']);
			$this->debug(3, "Intermediate(s): ". PHP_EOL . $this->backup['cab']);
			$this->updateStatus();
			return false;
		}
		
		$this->updateStatus();
        return true;
    }

    /**
     * Return the output handler
     *
     * @return Output_Output
     */
    public function output()
    {
        return $this->_output;
    }

    /**
     * Set the output handler object
     *
     * @param Output_Output $output  Output handler object
     *
     * @return DAOneClick
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

    /**
     * Update the status file, if enabled
     *
     * @return null
     */
    protected function updateStatus()
    {
        try {
            if (!$this->output()->status()->updateStatus($this->_domain)) {
                $this->debug(3, 'Skip writing status information to file');
            }
        } catch (RunTimeException $e) {
            $this->debug(1, $e->getMessage());
        }
    }
}