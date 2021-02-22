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
     * @var \Magento\Framework\Filesystem\DriverInterface | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $driver;

    protected function setUp(): void
    {
        $this->driver = $this->getMockForAbstractClass(\Magento\Framework\Filesystem\DriverInterface::class);
        $this->driver->expects($this->any())
            ->method('isExists')
            ->with($this->path)
            ->willReturn(true);
        $this->driver->expects($this->once())
            ->method('fileOpen')
            ->with($this->path, $this->mode)
            ->willReturn(null);
        $this->file = new Write($this->path, $this->driver, $this->mode);
    }

    protected function tearDown(): void
    {
        $this->file = null;
        $this->driver = null;
    }

    /**
     */
    public function testInstanceFileNotExists()
    {
        $this->expectException(\Magento\Framework\Exception\FileSystemException::class);

        $driver = $this->getMockForAbstractClass(\Magento\Framework\Filesystem\DriverInterface::class);
        $driver->expects($this->once())
            ->method('isExists')
            ->with($this->path)
            ->willReturn(false);
        $file = new Write($this->path, $driver, 'r');
        $this->assertInstanceOf(\Magento\Framework\Filesystem\File\Read::class, $file);
    }

    /**
     */
    public function testInstanceFileAlreadyExists()
    {
        $this->expectException(\Magento\Framework\Exception\FileSystemException::class);

        $driver = $this->getMockForAbstractClass(\Magento\Framework\Filesystem\DriverInterface::class);
        $driver->expects($this->once())
            ->method('isExists')
            ->with($this->path)
            ->willReturn(true);
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
            ->willReturn($result);
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
            ->willReturn($result);
        $this->assertEquals($result, $this->file->writeCsv($data, $delimiter, $enclosure));
    }

    public function testFlush()
    {
        $result = true;
        $this->driver->expects($this->once())
            ->method('fileFlush')
            ->with($this->resource)
            ->willReturn($result);
        $this->assertEquals($result, $this->file->flush());
    }

    /**
     */
    public function testWriteException()
    {
        $this->expectException(\Magento\Framework\Exception\FileSystemException::class);

        $data = 'data';
        $emptyTranslation = '';

        $this->driver->expects($this->once())
            ->method('fileWrite')
            ->with($this->resource, $data)
            ->willThrowException(new FileSystemException(new Phrase($emptyTranslation)));

        $this->file->write($data);
    }

    /**
     */
    public function testWriteCsvException()
    {
        $this->expectException(\Magento\Framework\Exception\FileSystemException::class);

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
     */
    public function testFlushException()
    {
        $this->expectException(\Magento\Framework\Exception\FileSystemException::class);

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
            ->willReturn($result);
        $this->assertEquals($result, $this->file->lock($lockMode));
    }

    public function testUnlock()
    {
        $result = true;
        $this->driver->expects($this->once())
            ->method('fileUnlock')
            ->with($this->resource)
            ->willReturn($result);
        $this->assertEquals($result, $this->file->unlock());
    }
}
