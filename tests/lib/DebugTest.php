<?php
class DebugTest extends PHPUnit_Framework_TestCase
{
    /**
     * setUp
     *
     * @return null
     */
    public function setUp()
    {
        vfsStream::setUp('debug');
    }

    /**
     * testNormalDebugOutputsString
     *
     * @covers Output_Debug::__construct
     * @covers Output_Debug::write
     *
     * @return null
     */
    public function testNormalDebugOutputsString()
    {
        vfsStreamWrapper::getRoot()->addChild(vfsStream::newFile('debug.log'));
        $fileRef = vfsStream::url('debug/debug.log');

        $dl = new Output_Debug(fopen($fileRef, 'w+'));
        $dl->write(0, 'My test');

        $this->assertEquals('My test', trim(file_get_contents($fileRef)));
    }

    /**
     * testDebugWithInsufficientLevelOutputsNothing
     *
     * @covers Output_Debug::__construct
     * @covers Output_Debug::write
     *
     * @return null
     */
    public function testDebugWithInsufficientLevelOutputsNothing()
    {
        vfsStreamWrapper::getRoot()->addChild(vfsStream::newFile('debug.log'));
        $fileRef = vfsStream::url('debug/debug.log');

        $dl = new Output_Debug(fopen($fileRef, 'w+'));
        $dl->write(1, 'Should not be output');

        $this->assertEmpty(trim(file_get_contents($fileRef)));
    }

    /**
     * testSetLevelLogsAtHigherLevel
     *
     * @covers Output_Debug::setLevel
     *
     * @return null
     */
    public function testSetLevelLogsAtHigherLevel()
    {
        vfsStreamWrapper::getRoot()->addChild(vfsStream::newFile('debug.log'));
        $fileRef = vfsStream::url('debug/debug.log');

        $dl = new Output_Debug(fopen($fileRef, 'w+'));
        $dl->setLevel(1)->write(1, 'Should be output now');

        $this->assertAttributeEquals(1, '_level', $dl);
        $this->assertEquals('Should be output now', trim(file_get_contents($fileRef)));
    }

    /**
     * testSetStatusWriterAppendsToStatusWriter
     *
     * @covers Output_Debug::setStatus
     * @covers Output_Debug::write
     *
     * @return null
     */
    public function testSetStatusWriterAppendsToStatusWriter()
    {
        vfsStreamWrapper::getRoot()->addChild(vfsStream::newFile('debug.log'));
        $fileRef = vfsStream::url('debug/debug.log');

        $swMock = $this->getMock('Output_Status', array('append'), array(), '', false);
        $swMock
            ->expects($this->once())
            ->method('append')
            ->with('debug', 'Output to status writer' . PHP_EOL);

        $dl = new Output_Debug(fopen($fileRef, 'w+'));
        $dl->setStatus($swMock)->write(0, 'Output to status writer');

        $this->assertAttributeEquals($swMock, '_status', $dl);
        $this->assertEquals('Output to status writer', trim(file_get_contents($fileRef)));
    }
}
