<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Cron\Queue;

use Magento\Setup\Model\Cron\Queue\Writer;

class WriterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem\Directory\ReadInterface
     */
    private $directoryWrite;

    /**
     * @var Writer
     */
    private $writer;

    public function setUp()
    {
        $this->filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $directoryRead = $this->getMockForAbstractClass(
            'Magento\Framework\Filesystem\Directory\ReadInterface',
            [],
            '',
            false
        );
        $this->directoryWrite = $this->getMockForAbstractClass(
            'Magento\Framework\Filesystem\Directory\WriteInterface',
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
