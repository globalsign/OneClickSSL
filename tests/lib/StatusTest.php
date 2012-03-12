<?php
class StatusTest extends PHPUnit_Framework_TestCase
{
    /**
     * setUp
     *
     * @return null
     */
    public function setUp()
    {
        vfsStream::setUp('tmp');
    }

    /**
     * testSetStatusPathSetsWritablePath
     *
     * @covers Output_Status::setStatusPath
     *
     * @return null
     */
    public function testSetStatusPathSetsWritablePath()
    {
        $dirRef = vfsStream::url('tmp');

        $sw = new Output_Status();
        $sw->setStatusPath($dirRef);
        $this->assertAttributeEquals($dirRef, '_statusPath', $sw);
    }

    /**
     * testUpdateStatusWithoutWriteEnabledDoesNothing
     *
     * @covers Output_Status::updateStatus
     *
     * @return null
     */
    public function testUpdateStatusWithoutWriteEnabledDoesNothing()
    {
        $dirRef = vfsStream::url('tmp');

        $sw = new Output_Status();
        $sw->setStatusPath($dirRef);
        $sw->write('test', 'test');

        $this->assertFalse($sw->updateStatus('example.com'));
        $this->assertFileNotExists(vfsStream::url('tmp/example.com_status.json'));
    }

    /**
     * testUpdateStatusWithoutStatusPathDoesNothing
     *
     * @covers Output_Status::updateStatus
     *
     * @return null
     */
    public function testUpdateStatusWithoutStatusPathDoesNothing()
    {
        $sw = new Output_Status();
        $sw->setWriteStatus(true);
        $sw->write('test', 'test');

        $this->assertFalse($sw->updateStatus('example.com'));
        $this->assertFileNotExists(vfsStream::url('tmp/example.com_status.json'));
    }

    /**
     * testUpdateStatusOnUnwritableFileThrowsException
     *
     * @covers Output_Status::updateStatus
     *
     * @return null
     */
    public function testUpdateStatusOnUnwritableFileThrowsException()
    {
        $this->setExpectedException(
            'RunTimeException',
            'Error writing status update to \'vfs://tmp/example.com_status.json\''
        );

        $dir = vfsStreamWrapper::getRoot();
        $dir->addChild(vfsStream::newFile('example.com_status.json')->chmod(0400));

        $sw = new Output_Status();
        $sw->setStatusPath(vfsStream::url('tmp'));
        $sw->setWriteStatus(true);
        $sw->write('test', 'test');

        $sw->updateStatus('example.com');
    }

    /**
     * testUpdateStatusWritesJsonFile
     *
     * @covers Output_Status::setWriteStatus
     * @covers Output_Status::write
     * @covers Output_Status::updateStatus
     * @covers Output_Status::hasJson
     *
     * @return null
     */
    public function testUpdateStatusWritesJsonFile()
    {
        $dir = vfsStreamWrapper::getRoot();
        $dir->addChild(vfsStream::newFile('example.com_status.json')->chmod(0666));

        $sw = new Output_Status();
        $sw->setStatusPath(vfsStream::url('tmp'));
        $sw->setWriteStatus(true);
        $sw->write('test', 'test');

        $this->assertTrue($sw->updateStatus('example.com'));
        $this->assertRegExp(
            '/^\{"test":"test","timestamp":.*,"lastupdate":".*"\}$/',
            file_get_contents(vfsStream::url('tmp/example.com_status.json'))
        );
    }

    /**
     * testAppendCatenatesString
     *
     * @covers Output_Status::append
     *
     * @return null
     */
    public function testAppendCatenatesString()
    {
        $dir = vfsStreamWrapper::getRoot();
        $dir->addChild(vfsStream::newFile('example.com_status.json')->chmod(0666));

        $sw = new Output_Status();
        $sw->setStatusPath(vfsStream::url('tmp'));
        $sw->setWriteStatus(true);
        $sw->append('test', 'test');
        $sw->append('test', 'more test');

        $this->assertTrue($sw->updateStatus('example.com'));
        $this->assertRegExp(
            '/^\{"test":"testmore test","timestamp":.*,"lastupdate":".*"\}$/',
            file_get_contents(vfsStream::url('tmp/example.com_status.json'))
        );
    }

    /**
     * testCustomJsonEncodeDoesRightThing
     *
     * @covers Output_Status::buildJson
     *
     * @return null
     */
    public function testCustomJsonEncodeDoesRightThing()
    {
        $dir = vfsStreamWrapper::getRoot();
        $dir->addChild(vfsStream::newFile('example.com_status.json')->chmod(0666));

        $sw = $this->getMock('Output_Status', array('hasJson'), array());
        $sw->expects($this->once())
            ->method('hasJson')
            ->will($this->returnValue(false));
        $sw->setStatusPath(vfsStream::url('tmp'));
        $sw->setWriteStatus(true);
        $sw->write('test', 'test');

        $this->assertTrue($sw->updateStatus('example.com'));
        $this->assertRegExp(
            '/^\{"test":"test","timestamp":.*,"lastupdate":".*"\}$/',
            file_get_contents(vfsStream::url('tmp/example.com_status.json'))
        );
    }
}
