<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model\Cron\Queue;

use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Setup\Model\Cron\Queue\Reader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReaderTest extends TestCase
{
    /**
     * @var MockObject|Filesystem
     */
    private $filesystem;

    /**
     * @var MockObject|ReadInterface
     */
    private $directoryRead;

    /**
     * @var Reader
     */
    private $reader;

    protected function setUp(): void
    {
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->directoryRead = $this->getMockForAbstractClass(
            ReadInterface::class,
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

    public function testReadException()
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('must be a valid JSON');
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
