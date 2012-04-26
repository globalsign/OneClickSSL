<?php
/**
* GlobalSign OneclickSSL extention for F5 BIP-IP iControl
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
*/

// Please request your own platform id at a GlobalSign representive
define("PLATFORMID", "682770516900001");
define("KEYALGORITHM", "RSA");

define("F5HOSTNAME", "192.168.178.162");
define("F5WSDL", "https://". F5HOSTNAME ."/iControl/iControlPortal.cgi?WSDL=Management.KeyCertificate");
define("F5USERNAME", "admin");
define("F5PASSWORD", "admin");

// SSL Server profiles
//https://192.168.178.162/iControl/iControlPortal.cgi?WSDL=LocalLB.ProfileServerSSL

require("../../lib/OneClickSSL.php");

class F5OneClick implements OneClickSSLPlugin
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
        
        // certificate_import_from_pem
        //   Imports/installs the specified certificates from the given PEM-formatted data. 
        //   https://devcentral.f5.com/wiki/iControl.Management__KeyCertificate__certificate_import_from_pem.ashx
        
        // Export the certificates to the filesystem
        openssl_pkey_export($privateKey,$privkeySting);
        openssl_x509_export($certificate,$certString);
        if (strlen($cacert) > 10) {
            openssl_x509_export($cacert,$cacertString);
        }

        // Delete the current private key (overwrite does only work with the same key material)
        $arguments = array();
        $arguments['mode'] = 0;
        $arguments['cert_ids'][] = $this->_domain .'_OneClickSSL';
        
        if (!($this->doCallSoap(F5WSDL, 'key_delete', $arguments) === false)) {
            // We can't upload a new key when there are certificate with old key material      
            // Use the same arguments array as when deleting the key
            $response = $this->doCallSoap(F5WSDL, 'certificate_delete', $arguments);
            
            $this->debug(1, "Deleted existing OneClickSSL key material for this site.");
        }
        
        // Upload a new private key
        $arguments = array();
        $arguments['mode'] = 0;
        $arguments['cert_ids'][] = $this->_domain .'_OneClickSSL';
        $arguments['pem_data'][] = $privkeySting;
        $arguments['overwrite'] = true;
        
        if ($this->doCallSoap(F5WSDL, 'key_import_from_pem', $arguments) === false) {
            $this->debug(1, "Error while uploading private key to device.");
            return false;
            
        } else {
            $this->debug(1, "Private key uploaded to device.");
        }
        
        // Import certificate and intermediates, this does not import the private key
        $arguments = array();
        $arguments['mode'] = 0;
        $arguments['cert_ids'][] = $this->_domain .'_OneClickSSL';
        $arguments['pem_data'][] = $certString . $cacertString;
        $arguments['overwrite'] = true;
        
        if ($this->doCallSoap(F5WSDL, 'certificate_import_from_pem', $arguments) === false) {
            $this->debug(1, "Error while uploading cerificate bundle to device.");
            return false;
        } else {
            $this->debug(1, "Certificate bundle uploaded to device.");
        }
        
        exit;
    }
    
    /**
     * Make the SOAP call via SoapClient to the F5 BIP-IP
     */
    protected function doCallSoap($wsdl, $function, $arguments)
    {
        use_soap_error_handler(true);
        ini_set("soap.wsdl_cache_enabled", "0");

        try {
            $client = new SoapClient($wsdl, array('trace' => true,
                                                  'exceptions' => true,
                                                  'connection_timeout' => 30,
                                                  'login' => F5USERNAME,
                                                  'password' => F5PASSWORD));
            $response = $client->__soapCall($function, $arguments);

            // More advanced error information
            $this->debug(3, "Request:\n" . $client->__getLastRequest() . PHP_EOL);
            $this->debug(3, "Response:\n" . $client->__getLastResponse() . PHP_EOL);

            return $response;

        } catch (SoapFault $fault) {
            // Don't report error on deletion, errors come when there is nothing to delete
            // we can only check for existing certificates by requesting them all.
            
            if ($function != 'key_delete' && $function != 'certificate_delete') {
                // There was a problem with the connection, WSDL or something else in with SOAP
                $this->debug(1, "Error in communication with the F5 BIG-IP (". $function .")");
            }
                
            $this->debug(3, "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})");
            return false;
        }
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
     * @return F5OneClick
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
$certData = new CertificateData('f5.paul.vanbrouwershaven.com',
                                'paul.vanbrouwershaven@globalsign.com',
                                '5daytrialDV');
  
// Enable the Remote Administration Agent(RAA) if enabled for your platform
//$certData->setRaa(1);

// The username that should be reffered to by the Remote Administration Agent(RAA)
// You only need to provide this information in the initial order when the RAA is enabled
//$certData->setUsr('username');
  
$oneclick = OneClickSSL::init($certData, new F5OneClick());

// How much output do you want?
$oneclick->output()->debug()->setLevel(1);

// Run on production (0), testing (1) or staging server (2)
//$oneclick->setEnvironment(1);

// Write procgress into status file (default: 0)
//$oneclick->output()->status()->setStatusPath(realpath('/tmp/'))->setWriteStatus(true);

$oneclick->order();
