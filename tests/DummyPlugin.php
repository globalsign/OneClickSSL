<?php
/**
 * DummyPlugin object for the purposes of setting up mock objects
 *
 * @uses OneClickSSLPlugin
 * @category OneClickSSL
 * @package  OneClickSSL
 */
class DummyPlugin implements OneClickSSLPlugin
{
    public function setDomain($domain)
    {

    }

    public function setOutput(Output_Output $output)
    {

    }

    public function install($privateKey, $certificate, $cacert = null)
    {

    }

    public function checkIp()
    {

    }

    public function backup()
    {

    }

    public function restoreBackup()
    {

    }
}
