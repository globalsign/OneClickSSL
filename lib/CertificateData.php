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
 * CertificateData
 */
class CertificateData
{
    const DOMAIN_PCRE      = '/^[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,15}(:[0-9]{1,5})?(\/.*)?$/i';

    const EMAIL_PCRE       = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/i';

    const VOUCHER_PCRE     = '/^[a-z0-9]{5,255}$/i';

    const DEFAULT_LANG     = 'en';

    const DEFAULT_SSL_PORT = 443;

    /**
     * List of allowed language codes
     *
     * @param array
     */
    protected static $_allowedLangs = array('en', 'nl', 'de', 'ja', 'zh', 'ko', 'fr', 'es');

    /**
     * Domain name for certificate
     *
     * @var string
     */
    protected $_domain;

    /**
     * SSL Port. Defaults to 443.
     *
     * @var int
     */
    protected $_port;

    /**
     * Email for domain administrator
     *
     * @var string
     */
    protected $_email;

    /**
     * Voucher for SSL certificate. May also be serial code for revoking a certificate.
     *
     * @var string
     */
    protected $_voucher;

    /**
     * Language to use see self::_allowedLangs for accepted languages. Defaults to 'en'
     *
     * @var string
     */
    protected $_lang;

    /**
     * Remote Admin Access flag
     *
     * @var bool
     */
    protected $_raa = false;

    /**
     * User string for RAA validation. This value depends entirely on the remote system
     *
     * @var string
     */
    protected $_usr;

    /**
     * Pass in dependent data, validate it and assign it to properties
     *
     * @param string $domain   Domain name for SSL
     * @param string $email    Email address for domain administrator
     * @param string $voucher  Voucher code or serial number
     * @param int    $port     SSL port number
     * @param string $lang     Language
     */
    public function __construct($domain, $email, $voucher, $port = self::DEFAULT_SSL_PORT, $lang = self::DEFAULT_LANG)
    {
        $this->_domain  = $this->validateDomain($domain);
        $this->_email   = $this->validateEmail($email);
        $this->_voucher = $this->validateVoucher($voucher);
        $this->_port    = $this->validatePort($port);
        $this->_lang    = $this->validateLang($lang);
    }

    /**
     * Return the domain name
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->_domain;
    }

    /**
     * Return the email address
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->_email;
    }

    /**
     * Return the voucher code (could be a serial number in the case of a revoke)
     *
     * @return string
     */
    public function getVoucher()
    {
        return $this->_voucher;
    }

    /**
     * Return the port number
     *
     * @return int
     */
    public function getPort()
    {
        return $this->_port;
    }

    /**
     * Return the language code being used
     *
     * @return string
     */
    public function getLang()
    {
        return $this->_lang;
    }

    /**
     * Return the RAA flag
     *
     * @return bool
     */
    public function getRaa()
    {
        return $this->_raa;
    }

    /**
     * Return the user validation string
     *
     * @return string
     */
    public function getUsr()
    {
        return $this->_usr;
    }

    /**
     * Set with RAA is enabled or not
     *
     * @param bool $value  Flag for if is enabled
     *
     * @return null
     */
    public function setRaa($value)
    {
        $this->_raa = (bool) $value;
    }

    /**
     * Set the user validation string
     *
     * @param string $value  The user validation string
     *
     * @return null
     */
    public function setUsr($value)
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException('Invalid user validation string');
        }

        // The user validation string could be a variety of things so the best thing
        // we can do here is just escape it. And be careful of what uses this.
        $this->_usr = addslashes($value);
    }

    /**
     * Validate and set the domain
     *
     * @param string $value  Expected domain name
     *
     * @return string
     */
    protected function validateDomain($value)
    {
        if (!preg_match(self::DOMAIN_PCRE, $value)) {
            throw new InvalidArgumentException('Invalid domain name');
        }

        return $value;
    }

    /**
     * Set the mail address
     *
     * @param string $value  The Email address to set
     *
     * @return OneClickSSL
     */
    public function validateEmail($value)
    {
        if (!preg_match(self::EMAIL_PCRE, $value)) {
            throw new InvalidArgumentException('Invalid email address');
        }

        return $value;
    }

    /**
     * Set the voucher to retrieve the certificate
     * Trial Voucher: 5daytrialDV
     *
     * @param string $value  Alphanumeric voucher code
     *
     * @return OneClickSSL
     */
    protected function validateVoucher($value)
    {
        if (!preg_match(self::VOUCHER_PCRE, $value)) {
            throw new InvalidArgumentException('Invalid voucher code or serial number');
        }

        return $value;
    }

    /**
     * Set the port number
     *
     * @param int $value  Port number to set
     *
     * @return int
     */
    protected function validatePort($value)
    {
        if (is_null($value)) {
            $value = self::DEFAULT_SSL_PORT;
        }

        if (!ctype_digit($value)){
            throw new InvalidArgumentException('Supplied port is not a number');
        }

        return (int) $value;
    }

    /**
     * Set the language code
     *
     * @param string $value  Language code
     *
     * @return string
     */
    protected function validateLang($value)
    {

        if (!in_array($value, self::$_allowedLangs)) {
            $value = self::DEFAULT_LANG;
        }

        return $value;
    }
}
