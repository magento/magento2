<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filesystem\Test\Unit\File;

use \Magento\Framework\Filesystem\File\Read;

/**
 * Class ReadTest
 */
class ReadTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Read
     */
    protected $file;

    /**
     * @var string
     */
    protected $path = 'path';

    /**
     * @var resource
     */
    protected $resource;

    /**
     * @var string
     */
    protected $mode = 'r';

    /**
     * @var \Magento\Framework\Filesystem\DriverInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $driver;

    protected function setUp()
    {
        $this->driver = $this->getMockForAbstractClass('Magento\Framework\Filesystem\DriverInterface');
        $this->resource = $this->getMock('resource');
        $this->driver->expects($this->any())
            ->method('isExists')
            ->with($this->path)
            ->will($this->returnValue(true));
        $this->driver->expects($this->once())
            ->method('fileOpen')
            ->with($this->path, $this->mode)
            ->will($this->returnValue($this->resource));
        $this->file = new Read($this->path, $this->driver);
    }

    public function tearDown()
    {
        $this->file = null;
        $this->driver = null;
    }

    /**
     * @expectedException \Magento\Framework\Exception\FileSystemException
     */
    public function testInstanceFileNotExists()
    {
        $driver = $this->getMockForAbstractClass('Magento\Framework\Filesystem\DriverInterface');
        $driver->expects($this->once())
            ->method('isExists')
            ->with($this->path)
            ->will($this->returnValue(false));
        $file = new Read($this->path, $driver);
        $this->assertInstanceOf('Magento\Framework\Filesystem\File\Read', $file);
    }

    public function testRead()
    {
        $length = 5;
        $result = 'content';
        $this->driver->expects($this->once())
            ->method('fileRead')
            ->with($this->resource, $length)
            ->will($this->returnValue($result));
        $this->assertEquals($result, $this->file->read($length));
    }

    public function testReadAll()
    {
        $flag = 5;
        $context = null;
        $result = 'content';
        $this->driver->expects($this->once())
            ->method('fileGetContents')
            ->with($this->path, $flag, $context)
            ->will($this->returnValue($result));
        $this->assertEquals($result, $this->file->readAll($flag, $context));
    }

    public function testReadLine()
    {
        $length = 5;
        $ending = '\n';
        $result = 'content';
        $this->driver->expects($this->once())
            ->method('fileReadLine')
            ->with($this->resource, $length, $ending)
            ->will($this->returnValue($result));
        $this->assertEquals($result, $this->file->readLine($length, $ending));
    }

    public function testReadCsv()
    {
        $length = 0;
        $delimiter = ',';
        $enclosure = '"';
        $escape = '\\';
        $result = 'content';
        $this->driver->expects($this->once())
            ->method('fileGetCsv')
            ->with($this->resource, $length, $delimiter, $enclosure, $escape)
            ->will($this->returnValue($result));
        $this->assertEquals($result, $this->file->readCsv($length, $delimiter, $enclosure, $escape));
    }

    public function testTell()
    {
        $result = 'content';
        $this->driver->expects($this->once())
            ->method('fileTell')
            ->with($this->resource)
            ->will($this->returnValue($result));
        $this->assertEquals($result, $this->file->tell());
    }

    public function testEof()
    {
        $result = 'content';
        $this->driver->expects($this->once())
            ->method('endOfFile')
            ->with($this->resource)
            ->will($this->returnValue($result));
        $this->assertEquals($result, $this->file->eof());
    }

    public function testClose()
    {
        $result = 'content';
        $this->driver->expects($this->once())
            ->method('fileClose')
            ->with($this->resource)
            ->will($this->returnValue($result));
        $this->assertEquals($result, $this->file->close());
    }

    public function testStat()
    {
        $result = 'content';
        $this->driver->expects($this->once())
            ->method('stat')
            ->with($this->path)
            ->will($this->returnValue($result));
        $this->assertEquals($result, $this->file->stat());
    }

    public function testSeek()
    {
        $offset = 5;
        $whence = SEEK_SET;
        $result = 'content';
        $this->driver->expects($this->once())
            ->method('fileSeek')
            ->with($this->resource, $offset, $whence)
            ->will($this->returnValue($result));
        $this->assertEquals($result, $this->file->seek($offset, $whence));
    }
}
