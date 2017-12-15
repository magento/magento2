<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filesystem\Test\Unit\File;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\File\Write;
use Magento\Framework\Phrase;

/**
 * Class WriteTest
 */
class WriteTest extends \PHPUnit\Framework\TestCase
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

    protected function setUp()
    {
        $this->driver = $this->getMockForAbstractClass(\Magento\Framework\Filesystem\DriverInterface::class);
        $this->driver->expects($this->any())
            ->method('isExists')
            ->with($this->path)
            ->will($this->returnValue(true));
        $this->driver->expects($this->once())
            ->method('fileOpen')
            ->with($this->path, $this->mode)
            ->willReturn(null);
        $this->file = new Write($this->path, $this->driver, $this->mode);
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
        $driver = $this->getMockForAbstractClass(\Magento\Framework\Filesystem\DriverInterface::class);
        $driver->expects($this->once())
            ->method('isExists')
            ->with($this->path)
            ->will($this->returnValue(false));
        $file = new Write($this->path, $driver, 'r');
        $this->assertInstanceOf(\Magento\Framework\Filesystem\File\Read::class, $file);
    }

    /**
     * @expectedException \Magento\Framework\Exception\FileSystemException
     */
    public function testInstanceFileAlreadyExists()
    {
        $driver = $this->getMockForAbstractClass(\Magento\Framework\Filesystem\DriverInterface::class);
        $driver->expects($this->once())
            ->method('isExists')
            ->with($this->path)
            ->will($this->returnValue(true));
        $file = new Write($this->path, $driver, 'x');
        $this->assertInstanceOf(\Magento\Framework\Filesystem\File\Read::class, $file);
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
     * @expectedException \Magento\Framework\Exception\FileSystemException
     */
    public function testWriteException()
    {
        $data = 'data';
        $emptyTranslation = '';

        $this->driver->expects($this->once())
            ->method('fileWrite')
            ->with($this->resource, $data)
            ->willThrowException(new FileSystemException(new Phrase($emptyTranslation)));

        $this->file->write($data);
    }

    /**
     * @expectedException \Magento\Framework\Exception\FileSystemException
     */
    public function testWriteCsvException()
    {
        $data = [];
        $delimiter = ',';
        $enclosure = '"';
        $emptyTranslation = '';

        $this->driver->expects($this->once())
            ->method('filePutCsv')
            ->with($this->resource, $data, $delimiter, $enclosure)
            ->willThrowException(new FileSystemException(new Phrase($emptyTranslation)));

        $this->file->writeCsv($data, $delimiter, $enclosure);
    }

    /**
     * @expectedException \Magento\Framework\Exception\FileSystemException
     */
    public function testFlushException()
    {
        $emptyTranslation = '';

        $this->driver->expects($this->once())
            ->method('fileFlush')
            ->with($this->resource)
            ->willThrowException(new FileSystemException(new Phrase($emptyTranslation)));

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
