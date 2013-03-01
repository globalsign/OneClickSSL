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
 * OneClickService
 */
class OneClickService
{
    const SERVER_PROD  = 'https://system.globalsign.com/vc/ws/OneClickOrder?wsdl';

    const SERVER_TEST  = 'https://testsystem.globalsign.com/vc/ws/OneClickOrder?wsdl';

    const SERVER_STAG  = 'https://gas-eval1.globalsign.com:10001/vc/ws/OneClickOrder?wsdl';

    protected $_certData;

    protected $_server;



    private $_checkVoucher;

    private $_testOrder;

    private $_realOrder;

    private $_revokeCert;

    private $_privateKey;

    private $_csrTxt;

    private $_testCertificate;

    private $_testCaCert;

    private $_realCertificate;

    private $_realCaCert;



    public function __construct(CertificateData $certData, $environment, Output_Output $output)
    {
        $this->_certData = $certData;

        $servers = array(self::SERVER_PROD, self::SERVER_TEST, self::SERVER_STAG);
        $this->_server = isset($servers[$environment]) ? $servers[$environment] : $servers[0];

        $this->_output = $output;
    }

    /**
     * First we will contact GlobalSign Services with domain and voucher details
     */
    public function checkVoucher($revoke = false)
    {
        $this->debug(1, "Contacting GlobalSign to verify domain and voucher details");
        $arguments = array(
            'Request' => array(
                'Voucher'       => $this->_certData->getVoucher(),
                'VoucherOption' => $this->_certData->getEmail(),
                'DomainName'    => $this->_certData->getDomain(),
                'Revoke'        => (int) $revoke, // Needs to be 0 or 1
                'Language'      => $this->_certData->getLang(),
                'PortNumber'    => $this->_certData->getPort(),
                'PlatformID'    => PLATFORMID,
                'KeyAlgorithm'  => KEYALGORITHM,
            )
        );

        $this->_checkVoucher = $this->doCall('GSDomainVoucherCheckOC', $arguments);
        return ($this->getCheckVoucherResponseCode() == 0);
    }

    /**
     * getCheckVoucherResponseCode
     *
     * @return int
     */
    public function getCheckVoucherResponseCode()
    {
        $code = -1;
        if ($this->_checkVoucher) {
            // Is an XML node... cast to string for __toString method
           $code = (string) $this->_checkVoucher->Response->OrderResponseHeader->SuccessCode;
        }
        return (int) $code;
    }

    /**
     * getVoucherOrderId
     *
     * @return null
     */
    public function getVoucherOrderId()
    {
        return $this->_checkVoucher->Response->OrderID;
    }

    /**
     * getKeyLength
     *
     * @return null
     */
    public function getKeyLength()
    {
        return $this->_checkVoucher->Response->KeyLength;
    }

    /**
     * getPrivateKey
     *
     * @return null
     */
    public function getPrivateKey()
    {
        return $this->_privateKey;
    }

    /**
     * Send the CSR to GlobalSign for a temporary certificate
     */
    public function testOrder($orderId, $csr)
    {
        $this->debug(1, "Sending the CSR to GlobalSign for a temporary certificate (". $orderId .")");

        $arguments = array();
        $arguments['Request']['OrderID'] = $orderId;
        $arguments['Request']['CSR'] = $csr;

        $this->_testOrder = $this->doCall('GSOneClickOrderTest', $arguments);
        return ($this->getTestResponseCode() == 0);
    }

    /**
     * getTestResponseCode
     *
     * @return null
     */
    public function getTestResponseCode()
    {
        $code = -1;
        if ($this->_testOrder) {
            // Is an XML node... cast to string for __toString method
           $code = (string) $this->_testOrder->Response->OrderResponseHeader->SuccessCode;
        }
        return (int) $code;
    }

    /**
     * getTestOrderId
     *
     * @return null
     */
    public function getTestOrderId()
    {
        return $this->_testOrder->Response->OrderID;
    }

    /**
     * Send the CSR to GlobalSign for the real certificate
     */
    public function realOrder($orderId, $csr)
    {
        $this->debug(1, "Sending the CSR to GlobalSign for the real certificate (". $orderId .")");

        $arguments = array();
        $arguments['Request']['OrderID'] = $orderId;
        $arguments['Request']['CSR'] = $csr;

        $this->_realOrder = $this->doCall('GSOneClickOrderReal', $arguments);
        return ($this->getOrderResponseCode() == 0);
    }

    /**
     * getOrderResponseCode
     *
     * @return null
     */
    public function getOrderResponseCode()
    {
        $code = -1;
        if ($this->_realOrder) {
            // Is an XML node... cast to string for __toString method
           $code = (string) $this->_realOrder->Response->OrderResponseHeader->SuccessCode;
        }
        return (int) $code;

    }

    /**
     * Revoke the certificate
      - The serial of the certificate that needs to be revoked is provided as the "Voucher" in checkOrder
     */
    public function revokeCert($orderId)
    {
        $this->debug(1, "Requesting a revocation for ".  $orderId);

        $arguments = array();
        $arguments['Request']['OrderID'] = $orderId;

        $this->_revokeCert = $this->doCall('GSOneClickRevoke', $arguments);
        return ($this->getRevokeResponseCode() == 0);
    }

    /**
     * getRevokeResponseCode
     *
     * @return null
     */
    public function getRevokeResponseCode()
    {
        $code = -1;
        if ($this->_revokeCert) {
            // Is an XML node... cast to string for __toString method
            $code = (string) $this->_revokeCert->Response->OrderResponseHeader->SuccessCode;
        }
        return (int) $code;
    }

    /**
     * Generate a new private (and public) key pair
     */
    public function newPrivateKey($keyLength)
    {
        $this->debug(1, "Generating a new private (and public) key pair");
        $keySize = (int) $keyLength;

        // We should get a valid keysize
        if (!ctype_digit($keySize) || $keySize < 1024) {
            $this->debug(1, "Invalid key-size (". $keySize .")");
            return false;
        }

        $config = array('private_key_bits' => $keySize,
                        'encrypt_key' => false);

        $this->_privateKey = openssl_pkey_new($config);

        return $this->_privateKey;
    }

    /**
     * Generate a new CSR
     */
    public function newCsr($privateKey)
    {
        $this->debug(1, "Generating a new Certificate Signing Request (CSR) for ".  $this->_certData->getDomain());

        $dn = array(
            "countryName" => "UK",
            "stateOrProvinceName" => "Some",
            "localityName" => "Maidstone",
            "organizationName" => "GlobalSign OneclickSSL",
            "commonName" => $this->_certData->getDomain(),
            "emailAddress" => "info@globalsign.com"
        );

        // The first (not RAA requested) CSR needs a username and hash
        if ($this->_certData->getRaa() && strlen($this->_certData->getUsr()) > 1) {
            $dn["organizationalUnitName"] = $this->_certData->getUsr();
        }

        // Add PINREQUIRED if this CSR is genarted by a RAA call like below
        // https://myuser:hash@:2222/CMD_PLUGINS/OneClickSSL/order.raw?domain=AAA&voucher=BBB&email=CCC
        if ($this->_certData->getRaa() && strlen($this->_certData->getUsr()) < 1) {
            $dn["organizationalUnitName"] = "PINREQUIRED";
        }

        $csr = openssl_csr_new($dn, $privateKey);

        return $csr;
    }

    /**
     * setCsrTxt
     *
     * @param mixed $csrTxt
     * @return null
     */
    public function setCsrTxt($csrTxt)
    {
        $this->_csrTxt = $csrTxt;
    }

    /**
     * getCsrTxt
     *
     * @return null
     */
    public function getCsrTxt()
    {
        return $this->_csrTxt;
    }

    /**
     * getTestCertificate
     *
     * @return null
     */
    public function getTestCertificate()
    {
        if (empty($this->_testCertificate)) {
            $this->_testCertificate =
                $this->_testOrder->Response->OneClickOrderDetail->Fulfillment->ServerCertificate->X509Cert;
        }
        return $this->_testCertificate;
    }

    /**
     * getTestCaCert
     *
     * @return null
     */
    public function getTestCaCert()
    {
        if (empty($this->_testCaCert)) {
            // Extract the certificate and the intermediates from the test (temporary) order
            $this->_testCaCert = '';
            foreach ($this->_testOrder->Response->OneClickOrderDetail->Fulfillment->CACertificates->CACertificate as $value) {
                if ($value->CACertType == 'INTER') {
                    $this->_testCaCert .= (string) $value->CACert;
                }
            }
        }
        return $this->_testCaCert;
    }

    /**
     * getRealCertificate
     *
     * @return null
     */
    public function getRealCertificate()
    {
        if (empty($this->_realCertificate)) {
            $this->_realCertificate =
                $this->_realOrder->Response->OneClickOrderDetail->Fulfillment->ServerCertificate->X509Cert;
        }
        return $this->_realCertificate;
    }

    /**
     * getRealCaCert
     *
     * @return null
     */
    public function getRealCaCert()
    {
        if (empty($this->_realCaCert)) {
            // Extract the certificate and the intermediates from the real order
            $this->_realCaCert = '';
            foreach ($this->_realOrder->Response->OneClickOrderDetail->Fulfillment->CACertificates->CACertificate as $value) {
                if ($value->CACertType == 'INTER'){
                    $this->_realCaCert .= (string) $value->CACert;
                }
            }
        }
        return $this->_realCaCert;
    }

    /**
     * Check if the certificate is installed
     */
    public function checkInstall($certificate)
    {
    	// If this is the production certificate we don't have to wait till the certificate
    	// is installed on the server
    	$x509 = openssl_x509_parse($certificate);
    	if ($x509['issuer']['OU'] != "For Test Purposes Only") {
    		return true;
    	}
    
        // Test with openssl if the certificate is installed
        // because this is only natively supported in newer PHP versions
        $orgCertHash = trim(shell_exec("echo ". escapeshellarg($certificate) ." | /usr/bin/openssl x509 -noout -modulus | /usr/bin/openssl sha1"));
        if ($orgCertHash === "") {
    		$this->debug(1, "The OpenSSL command line has not given any results, probably we are restricted from execution.");
        	$this->debug(1, "Waiting a minute to give the server some time to load the certificate.");
            sleep(60);
            
            return true;
    	}
        $this->debug(1, "Verifing if certificate with hash ". $orgCertHash ." is installed");

        $continue = false;
        $certStatus = false;
        $i = 1;
        while (!$continue) {
            // "-servername" for SNI support (Mutiple certificates on a single IP)
            $certHash = trim(shell_exec("/usr/bin/openssl s_client -servername ". $this->_certData->getDomain() ." -host ". $this->_certData->getDomain() ." -port ".  $this->_certData->getPort()." < /dev/null 2>/dev/null  | /usr/bin/openssl x509 -noout -modulus | /usr/bin/openssl sha1"));
            //$certHash = trim(shell_exec("/usr/bin/openssl s_client -host ". escapeshellarg($this->_certData->getDomain()) ." -port ".  $this->_certData->getPort()." < /dev/null 2>/dev/null  | /usr/bin/openssl x509 -noout -modulus | /usr/bin/openssl sha1"));
            $this->debug(1, "The webserver gives us a certificate with hash: ". $certHash);
            if ($certHash == $orgCertHash) {
                $this->debug(1, "Webserver has installed the certificate");
                $continue = true;

            } elseif ($i >= 15) {
                $this->debug(1, "Webserver failed to install the certificate");
                $continue = true;
                return false;

            } else {
                $this->debug(1, "Waiting until webserver installed the certificate (". $i .")");
                $this->updateStatus();

                $i++;
                sleep(5);
            }
        }

        return true;
    }

    /**
     * Make the call to GlobalSign
     */
    protected function doCall($function, $arguments)
    {

        $this->debug(2, "Using server '{$this->_server}' for communication");

        // Do we have Soap?
        if (class_exists('SoapClient')) {
            $this->debug(2, "Using SoapClient for communication");
            return $this->doCallSoap($function, $arguments);

        // No Soap, test for Curl
        } elseif (function_exists('curl_init')) {
            $this->debug(2, "SoapClient not installed, using Curl for communication");
            return $this->doCallCurl($function, $arguments);

        // Nothing availible
        } else {
            $this->debug(1, "Unable to start communication, no SoapClient or Curl availible");
            return false;
        }
    }

    /**
     * Make the SOAP call via SoapClient to GlobalSign
     */
    protected function doCallSoap($function, $arguments)
    {
        use_soap_error_handler(true);
        ini_set("soap.wsdl_cache_enabled", "0");

        try {
            $client = new SoapClient($this->_server, array('trace' => true,
                                                           'exceptions' => true,
                                                           'connection_timeout' => 30));
            $response = $client->__soapCall($function, array($function => $arguments));

            // Simple error information
            // check for more (array)
            if ($response->Response->OrderResponseHeader->SuccessCode <> 0) {
                $this->debug(1, $response->Response->OrderResponseHeader->Errors->Error->ErrorCode .": ".
                                $response->Response->OrderResponseHeader->Errors->Error->ErrorMessage);
            }

            // More advanced error information
            $this->debug(3, "Request:\n" . $client->__getLastRequest() . PHP_EOL);
            $this->debug(3, "Response:\n" . $client->__getLastResponse() . PHP_EOL);
            $this->debug(3, "Success code:\n" . $response->Response->OrderResponseHeader->SuccessCode);

            return $response;

        } catch (SoapFault $fault) {

            // There was a problem with the connection, WSDL or something else in with SOAP
            $this->debug(1, "Error in communication with GlobalSign, please try again later");
            $this->debug(3, "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})");

            return false;
        }
    }

    /**
     * Make XML from SoapClient array
     */
    protected function buildXML($array)
    {
        $xml = '';
        foreach($array as $key => $value) {
            $xml .= '<'. $key .'>';
            if (is_array($value)) {
                $xml .= PHP_EOL;
                $xml .= $this->buildXML($value);
            } else {
                $xml .= $value;
            }
            $xml .= '</'. $key .'>';
            $xml .= PHP_EOL;
        }

        return $xml;
    }

    /**
     * Make the SOAP call via Curl (fallback) to GlobalSign
     */
    protected function doCallCurl($function, $arguments)
    {
        $xml = $this->buildXML($arguments);

        // Format SOAP request
        $request = <<<SOAP
<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:ns1="https://system.globalsign.com/vc/ws/">
    <SOAP-ENV:Body>
        <ns1:$function>
            $xml;
        </ns1:$function>
    </SOAP-ENV:Body>
</SOAP-ENV:Envelope>
SOAP;

        $headers = array(
                        'Content-Type: text/xml; charset=utf-8',
                        'Content-Length: ' . strlen($request),
                        'SOAPAction: "'. $function .'"',
                    );

        // Make 'manual' http call to the SOAP server
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, str_replace('?wsdl', '', $this->_server));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);

        $result = curl_exec($ch);

        // Did Curl returned an error?
        if(curl_errno($ch)) {
            // There was a problem with the connection, WSDL or something else in with SOAP
            $this->debug(1, "Error in communication with GlobalSign, please try again later");
            $this->debug(3, "CURL Error: ". curl_errno($ch) . ': ' . curl_error($ch));

            curl_close($ch);
            return false;
        }
        curl_close($ch);

        // Parse SOAP formatted response to the same level as SoapClient
        $xml = simplexml_load_string($result);
        $namespaces = $xml->getNamespaces(true);
        $soap = $xml->children($namespaces['soap']);
        $ns2 = $soap->Body->children($namespaces['ns2']);
        $response = $ns2->children();

        // Simple error information
        // check for more (array)
        if ($response->Response->OrderResponseHeader->SuccessCode <> 0) {
            $this->debug(1, $response->Response->OrderResponseHeader->Errors->Error->ErrorCode .": ".
                            $response->Response->OrderResponseHeader->Errors->Error->ErrorMessage);
        }

        // More advanced error information
        $this->debug(3, "Request:\n" . $request . PHP_EOL);
        $this->debug(3, "Response:\n" . $result . PHP_EOL);
        $this->debug(3, "Success code:\n" . $response->Response->OrderResponseHeader->SuccessCode);

        return $response;
    }

    /**
     * debug
     *
     * @param int    $level    Level to display message for
     * @param string $message  Message to display
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
            if (!$this->_output->status()->updateStatus($this->_certData->getDomain())) {
                $this->debug(3, 'Skip writing status information to file');
            }
        } catch (RunTimeException $e) {
            $this->debug(1, $e->getMessage());
        }
    }
}
