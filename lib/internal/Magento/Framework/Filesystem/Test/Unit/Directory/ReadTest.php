<?php
/**
 * Unit Test for \Magento\Framework\Filesystem\Directory\Read
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filesystem\Test\Unit\Directory;

class ReadTest extends \PHPUnit\Framework\TestCase
{
    /**
     * \Magento\Framework\Filesystem\Driver
     *
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $driver;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Read
     */
    protected $read;

    /**
     * \Magento\Framework\Filesystem\File\ReadFactory
     *
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $fileFactory;

    /**
     * Directory path
     *
     * @var string
     */
    protected $path;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->driver = $this->createMock(\Magento\Framework\Filesystem\Driver\File::class);
        $this->fileFactory = $this->createMock(\Magento\Framework\Filesystem\File\ReadFactory::class);
        $this->read = new \Magento\Framework\Filesystem\Directory\Read(
            $this->fileFactory,
            $this->driver,
            $this->path
        );
    }

    /**
     * Tear down
     */
    protected function tearDown(): void
    {
        $this->driver = null;
        $this->fileFactory = null;
        $this->read = null;
    }

    public function testIsExist()
    {
        $this->driver->expects($this->once())->method('isExists')->willReturn(true);
        $this->assertTrue($this->read->isExist('correct-path'));
    }

    public function testStat()
    {
        $this->driver->expects($this->once())->method('stat')->willReturn(['some-stat-data']);
        $this->assertEquals(['some-stat-data'], $this->read->stat('correct-path'));
    }

    public function testReadFile()
    {
        $path = 'filepath';
        $flag = 'flag';
        $context = 'context';
        $contents = 'contents';

        $this->driver->expects($this->once())
            ->method('getAbsolutePath')
            ->with($this->path, $path)
            ->willReturn($path);
        $this->driver->expects($this->once())
            ->method('fileGetContents')
            ->with($path, $flag, $context)
            ->willReturn($contents);

        $this->assertEquals($contents, $this->read->readFile($path, $flag, $context));
    }
}
