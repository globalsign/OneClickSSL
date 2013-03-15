<?php
/*
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

/*
 * Enable error reporting for debugging only
 * For fatal errors this needs to be enabled in '/usr/local/cpanel/3rdparty/etc/php.ini'
 */
if (!ini_get('display_errors')) {
    ini_set('display_errors', '1');
}

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header("Cache-Control: no-cache");
header("Pragma: no-cache");
header("Content-type: text/html");

global $usrSettings, $settings;

/**
 * Read plugin config file
 */
$config = '../../../../3rdparty/OneClickSSL/etc/oneclickssl.conf';
$settings = unserialize(file_get_contents($config));

/**
 * Read user config file
 */
$usrConfig = '../../../../3rdparty/OneClickSSL/etc/usr_'. $_ENV['USER'] .'.conf';
if (file_exists($usrConfig)) {
	$usrSettings = unserialize(@file_get_contents($usrConfig));
} else {
	$usrSettings = array();
}

/*
 * cPanel library
 */
include("/usr/local/cpanel/php/cpanel.php");

/**
 * Include the cPanel specific library
 */
require('../../../../3rdparty/OneClickSSL/lib/cpanel.php');

/**
 * Remote Administration Agent(RAA) or Manual request
 * https://myuser:hash@host.myserver.com:2083/frontend/x3/GlobalSign/order.live.php?domain=AAA&voucher=BBB&email=CCC
 */
if (strlen($_GET['domain']) > 2) {
    if (ctype_digit($settings['remote_admin']) && $settings['remote_admin'] > 0) {
        // Set information from RAA
        try {
            $certData = new CertificateData($_GET['domain'], $_GET['email'], $_GET['voucher'], null, 'EN');
            $certData->setRaa($settings['remote_admin']);
        } catch (InvalidArgumentException $e) {
            die($e->getMessage() . PHP_EOL);
        }
    } else {
        // User disabled RAA, was enabled before
        echo "Remote Administration Agent(RAA) is disabled for this user";
        exit;
    }

/**
 * Normal order started from the interface
 */
} else {
    try {
        // QueryString is different for a manual order
        $certData = new CertificateData($_GET['d'], $_GET['o'], $_GET['v'], null, 'EN');
        // Do only include RAA information if this is enabled by the user
        if (ctype_digit($settings['remote_admin']) && $settings['remote_admin'] > 0 && $settings['da_loginkey'] > 0) {
			// todo: set cpanel user
            $certData->setUsr('admin');
            $certData->setRaa($settings['remote_admin']);
        }
    } catch (InvalidArgumentException $e) {
        die($e->getMessage() . PHP_EOL);
    }
}

$oneclick = OneClickSSL::init($certData, new DAOneClick());

// What debug level do we want to use?
if (ctype_digit($usrSettings['debug_level'])) {
    $oneclick->output()->debug()->setLevel($usrSettings['debug_level']);
} else if (ctype_digit($settings['debug_level'])) {
    $oneclick->output()->debug()->setLevel($settings['debug_level']);
}
 
// Run on production, testing or staging server
if (ctype_digit($settings['environment']) && $settings['environment'] > 0) {
    $oneclick->setEnvironment($settings['environment']);
}

$oneclick->output()->status()->setStatusPath(realpath('../../../../3rdparty/OneClickSSL/tmp/'))->setWriteStatus(true);

// If the revoke flag is set it's a revoke operation instead
if (ctype_digit($_GET['r']) && $_GET['r'] > 0) {
    $oneclick->revoke();
} else {
    $oneclick->order();
}
