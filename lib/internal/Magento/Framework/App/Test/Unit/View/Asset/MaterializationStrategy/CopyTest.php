<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\View\Asset\MaterializationStrategy;

use Magento\Framework\App\View\Asset\MaterializationStrategy\Copy;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\View\Asset\LocalInterface;
use PHPUnit\Framework\TestCase;

class CopyTest extends TestCase
{
    /**
     * @var Copy
     */
    private $copyPublisher;

    protected function setUp(): void
    {
        $this->copyPublisher = new Copy();
    }

    public function testPublishFile()
    {
        $rootDir = $this->getMockBuilder(WriteInterface::class)
            ->getMock();
        $targetDir = $this->getMockBuilder(WriteInterface::class)
            ->getMock();
        $sourcePath = 'source/path/file';
        $destinationPath = 'destination/path/file';

        $rootDir->expects($this->once())
            ->method('copyFile')
            ->with(
                $sourcePath,
                $destinationPath,
                $targetDir
            )->willReturn(true);

        $this->assertTrue($this->copyPublisher->publishFile($rootDir, $targetDir, $sourcePath, $destinationPath));
    }

    public function testIsSupported()
    {
        $asset = $this->getMockBuilder(LocalInterface::class)
            ->getMock();
        $this->assertTrue($this->copyPublisher->isSupported($asset));
    }
}
