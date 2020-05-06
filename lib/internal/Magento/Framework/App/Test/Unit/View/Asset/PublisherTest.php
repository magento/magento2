<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\View\Asset;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\View\Asset\MaterializationStrategy;
use Magento\Framework\App\View\Asset\MaterializationStrategy\Factory;
use Magento\Framework\App\View\Asset\MaterializationStrategy\StrategyInterface;
use Magento\Framework\App\View\Asset\Publisher;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Directory\WriteFactory;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\View\Asset\File;
use PHPUnit\Framework\MockObject\MockObject;

use PHPUnit\Framework\TestCase;

class PublisherTest extends TestCase
{
    /**
     * @var Filesystem|MockObject
     */
    private $filesystem;

    /**
     * @var WriteInterface|MockObject
     */
    private $sourceDirWrite;

    /**
     * @var ReadInterface|MockObject
     */
    private $staticDirRead;

    /**
     * @var WriteInterface|MockObject
     */
    private $staticDirWrite;

    /**
     * @var Publisher
     */
    private $object;

    /**
     * @var MaterializationStrategy\Factory|MockObject
     */
    private $materializationStrategyFactory;

    /**
     * @var WriteFactory|MockObject
     */
    private $writeFactory;

    protected function setUp(): void
    {
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->materializationStrategyFactory =
            $this->createMock(Factory::class);
        $this->writeFactory = $this->createMock(WriteFactory::class);
        $this->object = new Publisher($this->filesystem, $this->materializationStrategyFactory, $this->writeFactory);

        $this->sourceDirWrite = $this->getMockForAbstractClass(
            WriteInterface::class
        );
        $this->staticDirRead = $this->getMockForAbstractClass(
            ReadInterface::class
        );
        $this->staticDirWrite = $this->getMockForAbstractClass(
            WriteInterface::class
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
            $this->getMockForAbstractClass(StrategyInterface::class);

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
     * @return File|MockObject
     */
    protected function getAsset()
    {
        $asset = $this->createMock(File::class);
        $asset->expects($this->any())
            ->method('getPath')
            ->willReturn('some/file.ext');
        $asset->expects($this->any())
            ->method('getSourceFile')
            ->willReturn('/root/some/file.ext');
        return $asset;
    }
}
