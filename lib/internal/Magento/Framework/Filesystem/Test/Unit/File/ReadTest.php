<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filesystem\Test\Unit\File;

use \Magento\Framework\Filesystem\File\Read;

/**
 * Class ReadTest
 */
class ReadTest extends \PHPUnit\Framework\TestCase
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
        $this->file = new Read($this->path, $this->driver);
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
        $file = new Read($this->path, $driver);
        $this->assertInstanceOf(\Magento\Framework\Filesystem\File\Read::class, $file);
    }

    public function testRead()
    {
        $length = 5;
        $result = 'content';
        $this->driver->expects($this->once())
            ->method('fileRead')
            ->with($this->resource, $length)
            ->willReturn($result);
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
            ->willReturn($result);
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
            ->willReturn($result);
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
            ->willReturn($result);
        $this->assertEquals($result, $this->file->readCsv($length, $delimiter, $enclosure, $escape));
    }

    public function testTell()
    {
        $result = 'content';
        $this->driver->expects($this->once())
            ->method('fileTell')
            ->with($this->resource)
            ->willReturn($result);
        $this->assertEquals($result, $this->file->tell());
    }

    public function testEof()
    {
        $result = 'content';
        $this->driver->expects($this->once())
            ->method('endOfFile')
            ->with($this->resource)
            ->willReturn($result);
        $this->assertEquals($result, $this->file->eof());
    }

    public function testClose()
    {
        $result = 'content';
        $this->driver->expects($this->once())
            ->method('fileClose')
            ->with($this->resource)
            ->willReturn($result);
        $this->assertEquals($result, $this->file->close());
    }

    public function testStat()
    {
        $result = 'content';
        $this->driver->expects($this->once())
            ->method('stat')
            ->with($this->path)
            ->willReturn($result);
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
            ->willReturn($result);
        $this->assertEquals($result, $this->file->seek($offset, $whence));
    }
}
