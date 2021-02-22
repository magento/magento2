<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\View\Asset;

use \Magento\Framework\App\View\Asset\Publisher;
use \Magento\Framework\App\View\Asset\MaterializationStrategy;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\DriverPool;

class PublisherTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit\Framework\MockObject\MockObject
     */
    private $filesystem;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $sourceDirWrite;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $staticDirRead;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $staticDirWrite;

    /**
     * @var \Magento\Framework\App\View\Asset\Publisher
     */
    private $object;

    /**
     * @var MaterializationStrategy\Factory |\PHPUnit\Framework\MockObject\MockObject
     */
    private $materializationStrategyFactory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $writeFactory;

    protected function setUp(): void
    {
        $this->filesystem = $this->createMock(\Magento\Framework\Filesystem::class);
        $this->materializationStrategyFactory =
            $this->createMock(\Magento\Framework\App\View\Asset\MaterializationStrategy\Factory::class);
        $this->writeFactory = $this->createMock(\Magento\Framework\Filesystem\Directory\WriteFactory::class);
        $this->object = new Publisher($this->filesystem, $this->materializationStrategyFactory, $this->writeFactory);

        $this->sourceDirWrite = $this->getMockForAbstractClass(
            \Magento\Framework\Filesystem\Directory\WriteInterface::class
        );
        $this->staticDirRead = $this->getMockForAbstractClass(
            \Magento\Framework\Filesystem\Directory\ReadInterface::class
        );
        $this->staticDirWrite = $this->getMockForAbstractClass(
            \Magento\Framework\Filesystem\Directory\WriteInterface::class
        );
        $this->filesystem->expects($this->any())
            ->method('getDirectoryRead')
            ->with(DirectoryList::STATIC_VIEW)
            ->willReturn($this->staticDirRead);
        $this->filesystem->expects($this->any())
            ->method('getDirectoryWrite')
            ->willReturn($this->staticDirWrite);
        $this->writeFactory->expects($this->any())->method('create')->willReturn($this->sourceDirWrite);
    }

    public function testPublishExistsBefore()
    {
        $this->staticDirRead->expects($this->once())
            ->method('isExist')
            ->with('some/file.ext')
            ->willReturn(true);
        $this->assertTrue($this->object->publish($this->getAsset()));
    }

    public function testPublish()
    {
        $this->staticDirRead->expects($this->once())
            ->method('isExist')
            ->with('some/file.ext')
            ->willReturn(false);
        $materializationStrategy =
            $this->createMock(\Magento\Framework\App\View\Asset\MaterializationStrategy\StrategyInterface::class);

        $this->materializationStrategyFactory->expects($this->once())
            ->method('create')
            ->with($this->getAsset())
            ->willReturn($materializationStrategy);
        $materializationStrategy->expects($this->once())
            ->method('publishFile')
            ->with($this->sourceDirWrite, $this->staticDirWrite, 'file.ext', 'some/file.ext')
            ->willReturn(true);

        $this->assertTrue($this->object->publish($this->getAsset()));
    }

    /**
     * Create an asset mock
     *
     * @return \Magento\Framework\View\Asset\File|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getAsset()
    {
        $asset = $this->createMock(\Magento\Framework\View\Asset\File::class);
        $asset->expects($this->any())
            ->method('getPath')
            ->willReturn('some/file.ext');
        $asset->expects($this->any())
            ->method('getSourceFile')
            ->willReturn('/root/some/file.ext');
        return $asset;
    }
}
