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

$err = "";

/**
 * Check PHP version
 */  
$version = explode('.', PHP_VERSION);
if ($version[0] < 5) {
	$err .= 'You are using a very old PHP version please upgrade your installation to use this plugin. Check <a href="http://help.directadmin.com/item.php?id=135">this manual</a> for mor information on how to upgrade.';
}
unset($version);

/**
 * Check for Curl
 */  
if (!function_exists('curl_init')) {
	$err .= 'You need <a href="http://curl.haxx.se/" target="_blank">cURL</a> to use this plugin.';
}

/**
 * Check SimpleXML
 */  
if (!function_exists(simplexml_load_string)) {
	$err .= 'ERROR: SimpleXML not availible'. PHP_EOL;
}

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
$cpanel = &new CPANEL();
$cpanel->api1("setvar","",array("dprefix=../")); 

$replace = array();
$replace['{{ voucher_url }}'] = htmlentities(strip_tags($settings['voucher_url']));

// get domain
$res = $cpanel->api2('DomainLookup', 'getbasedomains');
if ($res['cpanelresult']['event']['result'] > 0) {
	$domain = $res['cpanelresult']['data'][0]['domain'];
}

$replace['{{ domain }}'] = htmlentities(strip_tags(trim($_POST['domain'])));
$replace['{{ voucher }}'] = htmlentities(strip_tags(trim($_POST['voucher'])));
$replace['{{ email }}'] = htmlentities(strip_tags(trim($_POST['email'])));
$replace['{{ revoke }}'] = htmlentities(strip_tags(trim($_POST['revoke'])));

// if no mail address is set, use the default from this user account
if (empty($replace['{{ email }}'])) {
	$res = $cpanel->api2('CustInfo', 'displaycontactinfo');
	if ($res['cpanelresult']['event']['result'] > 0) {
		$replace['{{ email }}'] = $res['cpanelresult']['data'][0]['value'];
	}
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_GET['action'])) {
    $error = false;

    if (!@checkdnsrr(str_replace('*.', '', $replace['{{ domain }}']), 'ANY')) {
        $errorMsg .= "The hostname ". str_replace('*.', '', $replace['{{ domain }}']) ." does not revolve to an ip address.<br>". PHP_EOL;
        $error = true;
    }

    $mail = explode("@", $replace['{{ email }}'], 2);
    if (!@checkdnsrr($mail[1], 'ANY')) {
        $errorMsg .= "The domain '". $mail[1] ."' is not able to receive mail, can't resolve domain.<br>". PHP_EOL;
        $error = true;
    }

    if (strlen($_POST['voucher']) < 4) {
        $errorMsg .= "Invalid voucher, the voucher should be minimal 5 characters.<br>". PHP_EOL;
        $error = true;
    }
    
     if (!array_key_exists('subagree', $_POST)) {
        $errorMsg .= "Please check and agree the terms and conditions<br>". PHP_EOL;
        $error = true;
     }
    
    if ($error) {
        $err .= '<p><div style="color: red;">'. $errorMsg .'</div></p>'. PHP_EOL;
    }

/*
 * User settings
 */
} else if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_GET['action'] == 'settings') {
	$usrSettings = array();
	if ($_POST['remote_admin']) {
		$usrSettings['remote_admin'] = 1;
	} else {
		$usrSettings['remote_admin'] = 0;
	}

	$usrSettings['sni'] = $_POST['sni'];
	$usrSettings['auto_ip'] = $_POST['auto_ip'];
	$usrSettings['debug_level'] = $_POST['debug_level'];

	$fp = fopen($usrConfig, 'w');
	fwrite($fp, serialize($usrSettings));
	fclose($fp);
}

/**
 * Handle a post request
 */
if ($_SERVER['REQUEST_METHOD'] == 'POST' 
	&& $error == false
	&& !isset($_GET['action'])
	) {

    $tplFile = 'user_process.tpl';

/**
 * Revoke
 */
} elseif ($_GET['action'] == 'revoke'
	&& $_SERVER['REQUEST_METHOD'] == 'POST' 
	) {

	$tplFile = 'user_process.tpl';

} elseif ($_GET['action'] == 'revoke') {
    $replace['{{ domain }}'] = str_replace('www.', '', $domain);
    $tplFile = 'user_revoke.tpl';

/**
 * Settings
 */
} elseif ($_GET['action'] == 'settings') {
	if ($usrSettings['remote_admin'] === 1) {
		$replace['{{ remote_admin }}'] = ' checked';
	} else {
		$replace['{{ remote_admin }}'] = '';
	}
	if (ctype_digit($usrSettings['auto_ip'])) {
		$replace['{{ auto_ip }}'] = $usrSettings['auto_ip'];
	} else {
		$replace['{{ auto_ip }}'] = '';
	}
	if (ctype_digit($usrSettings['sni'])) {
		$replace['{{ sni }}'] = $usrSettings['sni'];
	} else {
		$replace['{{ sni }}'] = '';
	}
	if (ctype_digit($usrSettings['debug_level'])) {
		$replace['{{ debug_level }}'] = $usrSettings['debug_level'];
	} else {
		$replace['{{ debug_level }}'] = '';
	}
    $tplFile = 'user_settings.tpl';
 
/**
 * Default -> Order
 */
} else {
    $replace['{{ domain }}'] = str_replace('www.', '', $domain);
    $tplFile = 'user_index.tpl';
}

/**
 * Load translation and get current locale from cPanel
 * http://docs.cpanel.net/twiki/bin/view/ApiDocs/Api2/ApiLocale#Locale::get_user_locale
 */
$res = $cpanel->api2('Locale', 'get_user_locale');
define('LANGPATH', '../../../../3rdparty/OneClickSSL/lib/Languages/');
require('../../../../3rdparty/OneClickSSL/lib/i18n.php');
$replace = array_merge($replace, getTranslation($res['cpanelresult']['data'][0]['locale']));

/**
 * Load template
 */
$template = file_get_contents('../../../../3rdparty/OneClickSSL/skins/default/'. $tplFile);

/*
 * cPanel header
 */
$res = $cpanel->api1('Branding', 'include', array('stdheader.html') );
print $res['cpanelresult']['data']['result'];

/*
 * Show errors
 */
if (!empty($err)) {
	echo $err;
}

/*
 * Include files (Django style)
 */
preg_match_all("/\{% include \"([^\"]*)\" %\}/", $template , $includeMatch);
foreach ($includeMatch[1] as $include){
	$replace['{% include "'. $include .'" %}'] = file_get_contents('../../../../3rdparty/OneClickSSL/skins/default/'. basename($include));
}
echo strtr($template, $replace);

/*
 * cPanel footer
 */
$res = $cpanel->api1('Branding', 'include', array('stdfooter.html') );
print $res['cpanelresult']['data']['result'];
$cpanel->end();
