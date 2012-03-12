<?php
class CertificateDataTest extends PHPUnit_Framework_TestCase
{
    /**
     * testConstructWithInvalidDomainThrowsException
     *
     * @covers CertificateData::__construct
     * @covers CertificateData::validateDomain
     *
     * @return null
     */
    public function testConstructWithInvalidDomainThrowsException()
    {
        $this->setExpectedException('InvalidArgumentException', 'Invalid domain name');

        new CertificateData('not a valid domain', 'test@example.com', '0123456789ABCDEF', 443, 'en');
    }

    /**
     * testConstructWithInvalidEmailThrowsException
     *
     * @covers CertificateData::__construct
     * @covers CertificateData::validateEmail
     *
     * @return null
     */
    public function testConstructWithInvalidEmailThrowsException()
    {
        $this->setExpectedException('InvalidArgumentException', 'Invalid email');

        new CertificateData('www.example.com', 'not an email address', '0123456789ABCDEF', 443, 'en');
    }

    /**
     * testConstructWithInvalidVoucherThrowsException
     *
     * @covers CertificateData::__construct
     * @covers CertificateData::validateVoucher
     *
     * @return null
     */
    public function testConstructWithInvalidVoucherThrowsException()
    {
        $this->setExpectedException('InvalidArgumentException', 'Invalid voucher code or serial number');

        new CertificateData('www.example.com', 'test@example.com', 'abc', 443, 'en');
    }

    /**
     * testConstructWithInvalidPortThrowsException
     *
     * @covers CertificateData::__construct
     * @covers CertificateData::validatePort
     *
     * @return null
     */
    public function testConstructWithInvalidPortThrowsException()
    {
        $this->setExpectedException('InvalidArgumentException', 'Supplied port is not a number');

        new CertificateData('www.example.com', 'test@example.com', 'abcdefghijklmnopq', 'NaN', 'en');
    }

    /**
     * testConstructWithInvalidLangDefaultsToEn
     *
     * @covers CertificateData::__construct
     * @covers CertificateData::validateLang
     *
     * @return null
     */
    public function testConstructWithInvalidLangDefaultsToEn()
    {
        $certData = new CertificateData('www.example.com', 'test@example.com', 'abcdefghijklmnopq', 443, 'blah');

        $this->assertAttributeEquals('en', '_lang', $certData);
    }

    /**
     * testConstructWithCorrectDataInstantiatesObject
     *
     * @covers CertificateData::__construct
     * @covers CertificateData::validateDomain
     * @covers CertificateData::validateEmail
     * @covers CertificateData::validateVoucher
     * @covers CertificateData::validatePort
     * @covers CertificateData::validateLang
     * @covers CertificateData::getDomain
     * @covers CertificateData::getEmail
     * @covers CertificateData::getVoucher
     * @covers CertificateData::getPort
     * @covers CertificateData::getLang
     *
     * @return null
     */
    public function testConstructWithCorrectDataInstantiatesObject()
    {
        $certData = new CertificateData('www.example.com', 'test@example.com', 'abcdefghijklmnopq', 990, 'ja');

        $this->assertAttributeEquals('www.example.com', '_domain', $certData);
        $this->assertAttributeEquals('test@example.com', '_email', $certData);
        $this->assertAttributeEquals('abcdefghijklmnopq', '_voucher', $certData);
        $this->assertAttributeEquals(990, '_port', $certData);
        $this->assertAttributeEquals('ja', '_lang', $certData);
        $this->assertEquals('www.example.com', $certData->getDomain());
        $this->assertEquals('test@example.com', $certData->getEmail());
        $this->assertEquals('abcdefghijklmnopq', $certData->getVoucher());
        $this->assertEquals(990, $certData->getPort());
        $this->assertEquals('ja', $certData->getLang());
    }

    /**
     * testConstructWithoutPortSetsDefaultPort
     *
     * @covers CertificateData::validatePort
     *
     * @return null
     */
    public function testConstructWithoutPortSetsDefaultPort()
    {
        $certData = new CertificateData('www.example.com', 'test@example.com', 'abcdefghijklmnopq', null, 'en');

        $this->assertAttributeEquals(443, '_port', $certData);
    }

    /**
     * testSetRaaSetsAttributeCorrectly
     *
     * @covers CertificateData::setRaa
     * @covers CertificateData::getRaa
     *
     * @return null
     */
    public function testSetRaaSetsAttributeCorrectly()
    {
        $certData = new CertificateData('www.example.com', 'test@example.com', 'abcdefghijklmnopq');
        $certData->setRaa(true);

        $this->assertAttributeEquals(true, '_raa', $certData);
        $this->assertTrue($certData->getRaa());
    }

    /**
     * testSetUsrWithNonStringThrowsException
     *
     * @covers CertificateData::setUsr
     *
     * @return null
     */
    public function testSetUsrWithNonStringThrowsException()
    {
        $this->setExpectedException('InvalidArgumentException', 'Invalid user validation string');

        $certData = new CertificateData('www.example.com', 'test@example.com', 'abcdefghijklmnopq');
        $certData->setUsr(array('bogus data'));
    }

    /**
     * testSetUsrWithNonStringThrowsException
     *
     * @covers CertificateData::setUsr
     * @covers CertificateData::getUsr
     *
     * @return null
     */
    public function testSetUsrSetsAttributeCorrectly()
    {
        $usr = 'admin|barry:abcdef123456@';
        $certData = new CertificateData('www.example.com', 'test@example.com', 'abcdefghijklmnopq');
        $certData->setUsr($usr);

        $this->assertAttributeEquals($usr, '_usr', $certData);
        $this->assertEquals($usr, $certData->getUsr());
    }
}
