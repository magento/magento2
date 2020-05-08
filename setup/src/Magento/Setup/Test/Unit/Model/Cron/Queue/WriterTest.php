<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model\Cron\Queue;

use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Setup\Model\Cron\Queue\Writer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class WriterTest extends TestCase
{
    /**
     * @var MockObject|Filesystem
     */
    private $filesystem;

    /**
     * @var MockObject|ReadInterface
     */
    private $directoryWrite;

    /**
     * @var Writer
     */
    private $writer;

    protected function setUp(): void
    {
        $this->filesystem = $this->createMock(Filesystem::class);
        $directoryRead = $this->getMockForAbstractClass(
            ReadInterface::class,
            [],
            '',
            false
        );
        $this->directoryWrite = $this->getMockForAbstractClass(
            WriteInterface::class,
            [],
            '',
            false
        );
        $this->filesystem->expects($this->once())->method('getDirectoryRead')->willReturn($directoryRead);
        $this->filesystem->expects($this->once())->method('getDirectoryWrite')->willReturn($this->directoryWrite);
        $this->writer = new Writer($this->filesystem);
    }

    public function testWrite()
    {
        $this->directoryWrite->expects($this->once())->method('writeFile')->with('.update_queue.json', 'data');
        $this->writer->write('data');
    }
}
