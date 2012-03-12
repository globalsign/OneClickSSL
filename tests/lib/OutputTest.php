<?php
class TestOutput extends PHPUnit_Framework_TestCase
{
    /**
     * testCreateObjectSetsCorrectData
     *
     * @covers Output_Output::__construct
     * @covers Output_Output::debug
     * @covers Output_Output::status
     *
     * @return null
     */
    public function testCreateObjectSetsCorrectData()
    {
        $debug  = new Output_Debug();
        $status = new Output_Status();

        $output = new Output_Output($debug, $status);

        $this->assertAttributeEquals($debug, '_debug', $output);
        $this->assertAttributeEquals($status, '_status', $output);
        $this->assertAttributeEquals($status, '_status', $debug);
        $this->assertEquals($debug, $output->debug());
        $this->assertEquals($status, $output->status());
    }
}
