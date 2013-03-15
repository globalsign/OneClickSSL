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

/**
 * cPanel persmissions check
 */  
function checkacl ($acl) {
	$user = $_ENV['REMOTE_USER'];

	if ($user == "root") {
		return true;
	}

	$reseller = file_get_contents("/var/cpanel/resellers");
	foreach ( split( "\n", $reseller ) as $line ) {
		if ( preg_match( "/^$user:/", $line) ) {
			$line = preg_replace( "/^$user:/", "", $line);
			foreach ( split(",", $line )  as $perm ) {
				if ( $perm == "all" || $perm == $acl ) {
					return true;
				}
			}
		}
	}
	return false;
}

/**
 * Check cPanel permissions
 */  
if (!checkacl('all')) {
	header("Status: 403 Access denied");
	echo 'Access denied';
	exit;
}

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
 * Update config on post
 */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {	
	$settings = array();
	$settings['environment'] = $_POST['environment'];
	$settings['debug_level'] = $_POST['debug_level'];
	$settings['auto_ip'] = $_POST['auto_ip'];
	$settings['sni'] = $_POST['sni'];
	$settings['voucher_url'] = $_POST['voucher_url'];
	
	$fp = fopen($config, 'w');
	fwrite($fp, serialize($settings));
	fclose($fp);
}

$replace = array();
$replace['{{ environment }}'] = $settings['environment'];
$replace['{{ debug_level }}'] = $settings['debug_level'];
$replace['{{ voucher_url }}'] = $settings['voucher_url'];
$replace['{{ auto_ip }}'] = $settings['auto_ip'];
$replace['{{ sni }}'] = $settings['sni'];
$replace['{{ version }}'] = trim(file_get_contents('../../../../3rdparty/OneClickSSL/version.txt'));

/**
 * Load translation and get current locale from cPanel
 * http://docs.cpanel.net/twiki/bin/view/ApiDocs/Api2/ApiLocale#Locale::get_user_locale
 */
define('LANGPATH', '../../../../3rdparty/OneClickSSL/lib/Languages/');
require('../../../../3rdparty/OneClickSSL/lib/i18n.php');
$replace = array_merge($replace, getTranslation('en'));

/**
 * Load template
 */
$template = file_get_contents('../../../../3rdparty/OneClickSSL/skins/default/admin_index.tpl');

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
