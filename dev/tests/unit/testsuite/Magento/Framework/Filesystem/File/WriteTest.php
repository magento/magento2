<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filesystem\File;

/**
 * Class WriteTest
 */
class WriteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Write
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
    protected $mode = 'w';

    /**
     * @var \Magento\Framework\Filesystem\DriverInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $driver;

    public function setUp()
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
        $this->file = new Write($this->path, $this->driver, $this->mode);
    }

    public function tearDown()
    {
        $this->file = null;
        $this->driver = null;
    }

    /**
     * @expectedException \Magento\Framework\Filesystem\FilesystemException
     */
    public function testInstanceFileNotExists()
    {
        $driver = $this->getMockForAbstractClass('Magento\Framework\Filesystem\DriverInterface');
        $driver->expects($this->once())
            ->method('isExists')
            ->with($this->path)
            ->will($this->returnValue(false));
        $file = new Write($this->path, $driver, 'r');
        $this->assertInstanceOf('Magento\Framework\Filesystem\File\Read', $file);
    }

    /**
     * @expectedException \Magento\Framework\Filesystem\FilesystemException
     */
    public function testInstanceFileAlreadyExists()
    {
        $driver = $this->getMockForAbstractClass('Magento\Framework\Filesystem\DriverInterface');
        $driver->expects($this->once())
            ->method('isExists')
            ->with($this->path)
            ->will($this->returnValue(true));
        $file = new Write($this->path, $driver, 'x');
        $this->assertInstanceOf('Magento\Framework\Filesystem\File\Read', $file);
    }

    public function testWrite()
    {
        $result = 4;
        $data = 'data';
        $this->driver->expects($this->once())
            ->method('fileWrite')
            ->with($this->resource, $data)
            ->will($this->returnValue($result));
        $this->assertEquals($result, $this->file->write($data));
    }

    public function testWriteCsv()
    {
        $data = [];
        $delimiter = ',';
        $enclosure = '"';
        $result = 0;
        $this->driver->expects($this->once())
            ->method('filePutCsv')
            ->with($this->resource, $data, $delimiter, $enclosure)
            ->will($this->returnValue($result));
        $this->assertEquals($result, $this->file->writeCsv($data, $delimiter, $enclosure));
    }

    public function testFlush()
    {
        $result = true;
        $this->driver->expects($this->once())
            ->method('fileFlush')
            ->with($this->resource)
            ->will($this->returnValue($result));
        $this->assertEquals($result, $this->file->flush());
    }

    /**
     * @expectedException \Magento\Framework\Filesystem\FilesystemException
     */
    public function testWriteException()
    {
        $data = 'data';
        $this->driver->expects($this->once())
            ->method('fileWrite')
            ->with($this->resource, $data)
            ->will($this->throwException(new \Magento\Framework\Filesystem\FilesystemException()));
        $this->file->write($data);
    }

    /**
     * @expectedException \Magento\Framework\Filesystem\FilesystemException
     */
    public function testWriteCsvException()
    {
        $data = [];
        $delimiter = ',';
        $enclosure = '"';
        $this->driver->expects($this->once())
            ->method('filePutCsv')
            ->with($this->resource, $data, $delimiter, $enclosure)
            ->will($this->throwException(new \Magento\Framework\Filesystem\FilesystemException()));
        $this->file->writeCsv($data, $delimiter, $enclosure);
    }

    /**
     * @expectedException \Magento\Framework\Filesystem\FilesystemException
     */
    public function testFlushException()
    {
        $this->driver->expects($this->once())
            ->method('fileFlush')
            ->with($this->resource)
            ->will($this->throwException(new \Magento\Framework\Filesystem\FilesystemException()));
        $this->file->flush();
    }

    public function testLock()
    {
        $lockMode = LOCK_EX;
        $result = true;
        $this->driver->expects($this->once())
            ->method('fileLock')
            ->with($this->resource, $lockMode)
            ->will($this->returnValue($result));
        $this->assertEquals($result, $this->file->lock($lockMode));
    }

    public function testUnlock()
    {
        $result = true;
        $this->driver->expects($this->once())
            ->method('fileUnlock')
            ->with($this->resource)
            ->will($this->returnValue($result));
        $this->assertEquals($result, $this->file->unlock());
    }
}
