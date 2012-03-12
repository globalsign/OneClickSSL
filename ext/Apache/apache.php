<?php
/**
* GlobalSign OneclickSSL extention for Apache
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

// Debian
define("PPATH", "/etc/ssl/private/");
define("CPATH", "/etc/ssl/certs/");
define("STATUSPATH", "./");

define("CONFIGDIR", "/etc/apache2/sites-enabled/");

define("SERVER", "https://gas-eval1.globalsign.com:10001/vc/ws/OneClickOrder?wsdl");
//define("SERVER", "https://system.globalsign.com/vc/ws/OneClickOrder?wsdl");

//define("PLATFORMID", "159083740800001PHP");
define("PLATFORMID", "642889112500001");// centos line break problem
define("KEYALGORITHM", "RSA");

define("STATUSPATH", "./");

require("oneclick.php");

class ApacheOneClick extends OneClickSSL {
    /**
     * Install the certificate
     */
    public function doInstall($privateKey, $orderResult)
    {
        $this->debug(1, "Preparing certificate installation for ". $this->_domain);

        // We can't install the certificates because the order failed
        if ($orderResult->Response->OrderResponseHeader->SuccessCode <> 0) {
            echo "We can't install the certificates because the order failed". PHP_EOL;
            return false;
        }

        // Extract the certificate and the intermediates from the test order
        $cacert = '';
        $certificate = $orderResult->Response->OneClickOrderDetail->Fulfillment->ServerCertificate->X509Cert;
        foreach ($orderResult->Response->OneClickOrderDetail->Fulfillment->CACertificates->CACertificate as $value){
            if ($value->CACertType == "INTER"){
                $cacert .= (string)$value->CACert;
            }
        }

        // Just some debugging information
        $this->debug(1, "Exporting certificates to the file system");
        $this->debug(2, "Certificate:\n". $certificate);
        $this->debug(2, "Intermediates:\n". $cacert);

        // Where do we store the certificates
        // - You can change this path at the top of this file
        $privkeyfile = PPATH . $this->_domain . ".key";
        $certfile = CPATH . $this->_domain . ".crt";
        $cacertfile = CPATH . $this->_domain . "_ca.crt";

        // Export the certificates to the filesystem
        openssl_pkey_export_to_file($privateKey,$privkeyfile);
        openssl_x509_export_to_file($certificate,$certfile);
        openssl_x509_export_to_file($cacert,$cacertfile);

        // Check the Apache configuration for mod_ssl or mod_gnutls
        // - Should we really do this every time?
        $sslModule = shell_exec("/usr/sbin/apache2ctl -D DUMP_MODULES");
        if (!preg_match("/ssl_module|gnutls_module/i", $sslModule)) {
            echo "Please make sure your Apache webserver is correctly configured". PHP_EOL;
        }

        // Reuqest a parsable list of virtual hosts that match or domain
        $vhostSites = shell_exec("apache2ctl -D DUMP_VHOSTS | grep ". $this->_domain);
        if (preg_match("/\(([^\)]*)\)/i", $vhostSites, $file)) {
            $this->debug(1, "The website looks to be defined in: ". $file[1]);
        }

        // Do backup the current certificates
        $this->doReload();

        return true;
    }

    /**
     * Back the current certificates
     */
    public function doBackup()
    {
        // Where do we store the certificates
        // - You can change this path at the top of this file
        $privkeyfile = PPATH . $this->_domain . ".key";
        $certfile = CPATH . $this->_domain . ".crt";
        $cacertfile = CPATH . $this->_domain . "_ca.crt";

        // Make a backup of any extisting certificates
        if (file_exists($privkeyfile)) {
            if (!copy($privkeyfile, $privkeyfile . '_bak')) {
                echo "Failed to backup private key: ". $privkeyfile;
            }
        }
        if (file_exists($certfile)) {
            if (!copy($certfile, $certfile . '_bak')) {
                echo "Failed to backup certificate: ". $certfile;
            }
        }
        if (file_exists($cacertfile)) {
            if (!copy($cacertfile, $cacertfile . '_bak')) {
                echo "Failed to backup CA certifiactes: ". $cacertfile;
            }
        }
    }

    /**
     * Restore the backup certificates
     */
    public function restoreBackup()
    {
        // Where do we store the certificates
        // - You can change this path at the top of this file
        $privkeyfile = PPATH . $this->_domain . ".key";
        $certfile = CPATH . $this->_domain . ".crt";
        $cacertfile = CPATH . $this->_domain . "_ca.crt";

        // Make a backup of any extisting certificates
        if (file_exists($privkeyfile)) {
            if (!copy($privkeyfile. '_bak', $privkeyfile)) {
                echo "Failed to restore private key: ". $privkeyfile;
            }
        }
        if (file_exists($certfile)) {
            if (!copy($certfile. '_bak', $certfile)) {
                echo "Failed to restore certificate: ". $certfile;
            }
        }
        if (file_exists($cacertfile)) {
            if (!copy($cacertfile. '_bak', $cacertfile)) {
                echo "Failed to restore CA certifiactes: ". $cacertfile;
            }
        }
    }

    /**
     * Reload the webserver
     */
    public function doReload()
    {
        // Check new configuratuon and restore the backup is there is an error
        exec("/usr/sbin/apache2ctl configtest", $configTest, $configTestResult);
        if ($configTestResult <> 0) {
            echo "Error while updating Apache configuration". PHP_EOL;

        } else {
            $this->debug(1, "Restarting the Apache webserver");

            // Restart the Apache webserver to load the certificate
            exec("/etc/init.d/apache2 reload");

            $this->debug(1, "Wait a few seconds so we are sure Apache is servering the certificates");

            // Wait a few seconds so Apache is servering the certificates
            sleep(5);
        }
    }
}

// check/create dir -p /etc/ssl/oneclickssl/backup

$oneclick = new ApacheOneClick();
$oneclick->setDomain('remote.paul.vanbrouwershaven.com');
$oneclick->setVoucher('5daytrialDV');
$oneclick->setMail('paul@vanbrouwershaven.com');
//$oneclick->setPort(443);
//$oneclick->setLang('en');

// Debug outputting (default: 0)
$oneclick->setDebug(4);
$oneclick->setTest(1);

// Write procgress into status file (default: 0)
$oneclick->setWriteStatus(1);

if (!$oneclick->order()) {
    echo "Instllation error". PHP_EOL;
}
?>
