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
 * cPanel library
 
 */
include("/usr/local/cpanel/php/cpanel.php");

$cpanel = new CPANEL();

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header("Cache-Control: no-cache");
header("Pragma: no-cache");

header("Content-type: application/json");

$file = '../../../../3rdparty/OneClickSSL/tmp/'. basename($_GET['d']) .'_status.json';

if(is_file($file)) {
	echo file_get_contents($file);
} else {
	echo '{ "debug" : "Sorry, no status availible" }';
}

// remove file when status is finished