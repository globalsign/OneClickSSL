<?php
/**
* GlobalSign OneclickSSL Internationalization
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

// LANGPATH Should be defined before loading this file
//   the location is depended on the platform

/**
 * Get translation from Microsoft ResX Schema
 */
function getTranslation($lang) {
    if (!defined('LANGPATH')) {
        die('ERROR: LANGPATH is not defined'. PHP_EOL);
    }

    // Open file if exists, else use English
    $langFile = LANGPATH .'Translations_'. basename(strtolower($lang)) .'.resx';
    if (file_exists($langFile)) {
        $xml = file_get_contents($langFile);

    } else {
        $xml = file_get_contents(LANGPATH .'Translations_en.resx');
    }

    // Parse file with SimpleXML
    $resx = simplexml_load_string($xml);

    // Create array that can be merged with the template
    $translation = array();
    foreach ($resx->data as $value) {
        $translation['{{ LANG::'. (string)$value->attributes()->name .' }}'] = (string)$value->value;
    }

    return $translation;
}
