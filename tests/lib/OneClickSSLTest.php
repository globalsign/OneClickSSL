<?php
class OneClickSSLTest extends PHPUnit_Framework_TestCase
{
    /**
     * testConstructSetsDataCorrectly
     *
     * @covers OneClickSSL::__construct
     * @covers OneClickSSL::output
     *
     * @return null
     */
    public function testConstructSetsDataCorrectly()
    {
        $certData = new CertificateData('www.example.com', 'test@example.com', 'voucher');

        $output   = new Output_Output(new Output_Debug(), new Output_Status());

        $plugin   = $this->getMock('DummyPlugin', array('setOutput', 'setDomain'), array());
        $plugin
            ->expects($this->once())
            ->method('setOutput')
            ->with($output);
        $plugin
            ->expects($this->once())
            ->method('setDomain')
            ->with('www.example.com');

        $oneclick = new OneClickSSL($certData, $plugin, $output);

        $this->assertAttributeEquals($certData, '_certData', $oneclick);
        $this->assertAttributeEquals($plugin,   '_plugin',   $oneclick);
        $this->assertAttributeEquals($output,   '_output',   $oneclick);
        $this->assertEquals($output, $oneclick->output());
    }
}
