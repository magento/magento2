<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Cron\Queue;

use Magento\Setup\Model\Cron\Queue\Reader;

class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem\Directory\ReadInterface
     */
    private $directoryRead;

    /**
     * @var Reader
     */
    private $reader;

    public function setUp()
    {
        $this->filesystem = $this->getMock(\Magento\Framework\Filesystem::class, [], [], '', false);
        $this->directoryRead = $this->getMockForAbstractClass(
            \Magento\Framework\Filesystem\Directory\ReadInterface::class,
            [],
            '',
            false
        );
        $this->filesystem->expects($this->once())->method('getDirectoryRead')->willReturn($this->directoryRead);
        $this->reader = new Reader($this->filesystem);
    }

    public function testReadEmpty()
    {
        $this->directoryRead->expects($this->once())->method('isExist')->willReturn(false);
        $this->assertEquals('', $this->reader->read());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage must be a valid JSON
     */
    public function testReadException()
    {
        $this->directoryRead->expects($this->once())->method('isExist')->willReturn(true);
        $this->directoryRead->expects($this->once())->method('readFile')->willReturn('invalid json');
        $this->reader->read();
    }

    public function testRead()
    {
        $this->directoryRead->expects($this->once())->method('isExist')->willReturn(true);
        $this->directoryRead->expects($this->once())
            ->method('readFile')
            ->willReturn('{"jobs":[{"name": "job A", "params": []}]}');
        $this->assertEquals('{"jobs":[{"name": "job A", "params": []}]}', $this->reader->read());
    }
}
