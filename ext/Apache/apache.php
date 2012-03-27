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

define("PLATFORMID", "236368385400001");
define("KEYALGORITHM", "RSA");

// Read/write permissions for root only (chmod 600)
define("CERTDIR", "/etc/ssl/oneclickssl/");

require("../../lib/OneClickSSL.php");

class ApacheOneClick implements OneClickSSLPlugin
{
    protected $_output;

    protected $_domain;
    
    protected $_apache;
    
    protected $_apache2ctl;
    
    /**
     * Set up the object with the Plugin and Output handler
     */
    public function __construct()
    {
    	// Locate Apache init script
        if (is_executable('/etc/init.d/apache2')) {
    		$this->_apache = "/etc/init.d/apache2";
    		
    	} else if (is_executable("/etc/init.d/apache")) {
    		$this->_apache = "/etc/init.d/apache";

    	} else if (is_executable("/etc/init.d/httpd")) {
    		$this->_apache = "/etc/init.d/httpd";

	} else {
		$this->debug(1, "Can't find Apache init script, please create a symbolic link or update 'apache.php'");
    		return false;
	}
	
	
	// Locate apache2ctl
        if (is_executable('/usr/sbin/apache2ctl')) {
    		$this->_apache2ctl = "/usr/sbin/apache2ctl";
    		
    	} else if (is_executable("/usr/local/sbin/apache2ctl")) {
    		$this->_apache2ctl = "/usr/local/sbin/apache2ctl";

    	} else if (is_executable("/usr/local/apache2/bin/apachectl")) {
    		$this->_apache2ctl = "/usr/local/apache2/bin/apachectl";
    		
    	} else if (is_executable("/opt/apache2/bin/apachectl")) {
    		$this->_apache2ctl = "/opt/apache2/bin/apachectl";
    		
	} else {
		$this->debug(1, "Can't find apache2ctl, please create a symbolic link or update 'apache.php'");
    		return false;
	}
    }

    /**
     * Check for unique ip address
     */    
    public function checkIp() {
        $ip = gethostbyname($this->_domain);
        exec($this->_apache2ctl ." -D DUMP_VHOSTS 2>/dev/null | grep '". $ip .":80'", $vhostSites, $vhostSitesResult);
        if ($vhostSitesResult == 0 && count($vhostSites) === 1) {
            $this->debug(1, "This website is running on a dedicated IP address: ".$ip);
            return true;
            
        } else {
            $this->debug(1, "This website is not running on a dedicated IP address, please configure a dedicated ip first!");
            return false;
        }
    }
    
    /**
     * Install the certificate
     */
    public function install($privateKey, $certificate, $cacert = null)
    {
        $this->debug(1, "Preparing Apache certificate installation for ". $this->_domain);
                        
        // Just some debugging information
        $this->debug(1, "Exporting certificates to the file system");
        $this->debug(2, "Certificate:\n". $certificate);
        $this->debug(2, "Intermediates:\n". $cacert);

        // Export the certificates to the filesystem
        openssl_pkey_export_to_file($privateKey, CERTDIR . $this->_domain . ".key");
        openssl_x509_export_to_file($certificate, CERTDIR . $this->_domain . ".crt");
        if (strlen($cacert) > 10) {
            openssl_x509_export_to_file($cacert, CERTDIR . $this->_domain . "_ca.crt");
        }

        // Set rw file permissions for root only
        chmod(CERTDIR . $this->_domain . ".key", 600);
        chmod(CERTDIR . $this->_domain . ".crt", 600);
        chmod(CERTDIR . $this->_domain . "_ca.crt", 600);
        
        // Reuqest a parsable list of virtual hosts that match or domain
        exec($this->_apache2ctl ." -D DUMP_VHOSTS 2>/dev/null | grep '". gethostbyname($this->_domain) ."'", $vhostSites, $vhostSitesResult);
        if ($vhostSitesResult == 0 && preg_match_all("/([^\s]*)\s*[^\s]*\s\(([^:]*)/i", implode($vhostSites, PHP_EOL), $file)) {
            $ip = gethostbyname($this->_domain);
                       
            if ($key = array_search($ip .':443', $file[1])) {
                $vhostConfigFile = $file[2][$key];
                
                $this->debug(1, "We found an SSL configured virtual host for this website");
            } else {
                $vhostConfigFile = $file[2][0];
            }
            
            $this->debug(1, "This website is configured in: ". $vhostConfigFile);
            if (!file_exists($vhostConfigFile)) {
                $this->debug(1, "Can't open configuration file");
                return false;
            }
            
        } else {
            $this->debug(1, "Can't find configuration file for ". $this->_domain);
            return false;
        }

        // Updating in memory first
        $newConfig = "";
        $vhostSsl = false;

        // Create backup from config file
        copy($vhostConfigFile, '/tmp/'. basename($vhostConfigFile) .'.bak');
        
        // Open Apache config, and walk through it line by line
        $handle = @fopen($vhostConfigFile, "r");
        if ($handle) {
            while (($buffer = fgets($handle, 4096)) !== false) {               
                // Start of config block
                if (strstr($buffer, "<VirtualHost")) {
                    $vhost = true;
                    $vhostData = array();
                    $vhostBlock = "";
                }
                
                // Check which vhost and if we need to update
                if ($vhost) {
                    if (strstr($buffer, $ip)) {
                        $vhostData['ip'] = $ip;
                    }
                    if (strstr($buffer,$this->_domain)) {
                        $vhostData['domain'] = $this->_domain;
                    }
                    
                    if (array_key_exists('domain', $vhostData) 
                        && $vhostData['domain'] === $this->_domain) {
                    
                        $lineKeys = explode(' ', trim($buffer), 2);
                        switch(trim($lineKeys[0])) {
                            // Turn SSL on
                            case "SSLEngine":
                                $vhostSsl = true;
                                $vhostBlock .= "\tSSLEngine on" .PHP_EOL;
                                break;
                            
                            // Certificate
                            case "SSLCertificateFile":
                                $vhostSsl = true;
                                $vhostBlock .= "\tSSLCertificateFile ". CERTDIR . $this->_domain . ".crt" .PHP_EOL;
                                break;
                            
                            // Private key
                            case "SSLCertificateKeyFile":
                                $vhostSsl = true;
                                $vhostBlock .= "\tSSLCertificateKeyFile ". CERTDIR . $this->_domain . ".key" .PHP_EOL;
                                break;
                            
                            // Intermediate certificate(s) 
                            case "SSLCertificateChainFile":
                                // Ignore here if it's already set we add it later
                                $vhostSsl = true;
                                break;

                            default:
                                $vhostBlock .= $buffer;
                        }
                    } else {
                        $vhostBlock .= $buffer;
                    }
                }
                
                if (!$vhost) {
                    // We don't want to change this part but don't want to loose it either
                    $newConfig .= $buffer;
                }
                // End of config block
                if (strstr($buffer, "</VirtualHost>")) {
                    $vhost = false;
                    
                    // Remove </VirtualHost> to add SSL config
                    $vhostBlock = substr(trim($vhostBlock), 0, -14);
                    
                    // Add intermediate certificate(s) (also if not defined before)
                    if ($vhostSsl && strlen($cacert) > 10) {
                        $vhostBlock .= "\tSSLCertificateChainFile ". CERTDIR . $this->_domain . "_ca.crt" .PHP_EOL;
                    }
                    
                    // We did not found any SSL config, save to copy
                    if (!$vhostSsl) {
                    	$vhosts[$vhostData['ip']] = $vhostBlock;
                    }
                    
                    // Close virtualhost and include config
                    $vhostBlock .= "</VirtualHost>" .PHP_EOL;
                    $newConfig .= $vhostBlock;
                    
                    unset($vhostBlock);
                }
            }
        }
        
        // We did not found any ssl enabled virtual host, copy the host we found 
        if (!$vhostSsl && array_key_exists($ip, $vhosts)) {
            $newConfig .= "<IfModule mod_ssl.c>" .PHP_EOL;
            // Copy http config and change port number
            $newConfig .= preg_replace("/^(<VirtualHost\s[^:]*):80>/i", "\${1}:443>", $vhosts[$ip]);
            $newConfig .= "\tSSLEngine on" .PHP_EOL;
            $newConfig .= "\tSSLCertificateFile ". CERTDIR . $this->_domain . ".crt" .PHP_EOL;
            $newConfig .= "\tSSLCertificateKeyFile ". CERTDIR . $this->_domain . ".key" .PHP_EOL;
            if (strlen($cacert) > 10) {
                $newConfig .= "\tSSLCertificateChainFile ". CERTDIR . $this->_domain . "_ca.crt" .PHP_EOL;
            }
            $newConfig .= "\tSSLProtocol -ALL +SSLv3 +TLSv1" .PHP_EOL;
            $newConfig .= "\tSSLCipherSuite ALL:!ADH:RC4+RSA:+HIGH:+MEDIUM:-LOW:-SSLv2:-EXP" .PHP_EOL;
            $newConfig .= "</VirtualHost>" .PHP_EOL;
            $newConfig .= "</IfModule>" .PHP_EOL;
        }
        
        // Close config file
        fclose($handle);

        // Write the new config file to disk
        file_put_contents($vhostConfigFile, $newConfig);
        
        // Reload Apache
        exec($this->_apache ." reload", $configReload, $configReloadResult);
        
        if ($configReloadResult <> 0) {
            $this->debug(1, "Error while reloading Apache configuration");
            $this->debug(2, implode($configReload, PHP_EOL));
        
            // Restore and delete backup
            copy('/tmp/'. basename($vhostConfigFile) .'.bak', $vhostConfigFile);
            unlink('/tmp/'. basename($vhostConfigFile) .'.bak');
            return false;
        }

        // Delete backup
        unlink('/tmp/'. basename($vhostConfigFile) .'.bak');
        
        // Return certificate for installation check
        return $certificate;
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
     * Back the current certificates
     */     
    public function backup()
    {
        if (@copy(CERTDIR . $this->_domain .'.*', CERTDIR .'backup/')) {
            return true;
        } else {
            return false;
        }
    }
	
    /**
     * Restore the backup certificates
     */     
    public function restoreBackup()
    {
        if (@copy(CERTDIR .'backup/'. $this->_domain .'.*', CERTDIR)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Set the output handler object
     *
     * @param Output_Output $output  Output handler object
     *
     * @return ApacheOneClick
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


// Create certificate directory if not exists
if (!is_dir(CERTDIR .'backup')) {
	mkdir(CERTDIR, 600, true);
}

// Create certificate backup directory if not exists
if (!is_dir(CERTDIR .'backup')) {
	mkdir(CERTDIR .'backup', 600, true);
    
    // Check the Apache configuration for mod_ssl or mod_gnutls
    $sslModule = shell_exec($this->_apache2ctl ." -D DUMP_MODULES 2>/dev/null");
    if (!preg_match("/ssl_module|gnutls_module/i", $sslModule)) {
        echo "Please make sure your Apache webserver is correctly configured". PHP_EOL;
    }
}

/**
 * Initiate OneClickSSL Procedure
 *  $domain, $email, $voucher, $port = self::DEFAULT_SSL_PORT, $lang = self::DEFAULT_LANG 
 */
$certData = new CertificateData('remote.paul.vanbrouwershaven.com',
                                'paul.vanbrouwershaven@globalsign.com',
                                '5daytrialDV');
  
$oneclick = OneClickSSL::init($certData, new ApacheOneClick());

$oneclick->output()->debug()->setLevel(1);

// Run on production (0), testing (1) or staging server (2)
$oneclick->setEnvironment(1);

// Write procgress into status file (default: 0)
//$oneclick->output()->status()->setStatusPath(realpath('/tmp/'))->setWriteStatus(true);

$oneclick->order();