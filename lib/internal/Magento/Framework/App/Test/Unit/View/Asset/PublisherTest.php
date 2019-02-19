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
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystem;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sourceDirWrite;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $staticDirRead;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $staticDirWrite;

    /**
     * @var \Magento\Framework\App\View\Asset\Publisher
     */
    private $object;

    /**
     * @var MaterializationStrategy\Factory |\PHPUnit_Framework_MockObject_MockObject
     */
    private $materializationStrategyFactory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $writeFactory;

    protected function setUp()
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
            ->will($this->returnValue($this->staticDirRead));
        $this->filesystem->expects($this->any())
            ->method('getDirectoryWrite')
            ->will($this->returnValue($this->staticDirWrite));
        $this->writeFactory->expects($this->any())->method('create')->willReturn($this->sourceDirWrite);
    }

    public function testPublishExistsBefore()
    {
        $this->staticDirRead->expects($this->once())
            ->method('isExist')
            ->with('some/file.ext')
            ->will($this->returnValue(true));
        $this->assertTrue($this->object->publish($this->getAsset()));
    }

    public function testPublish()
    {
        $this->staticDirRead->expects($this->once())
            ->method('isExist')
            ->with('some/file.ext')
            ->will($this->returnValue(false));
        $materializationStrategy =
            $this->createMock(\Magento\Framework\App\View\Asset\MaterializationStrategy\StrategyInterface::class);

        $this->materializationStrategyFactory->expects($this->once())
            ->method('create')
            ->with($this->getAsset())
            ->will($this->returnValue($materializationStrategy));
        $materializationStrategy->expects($this->once())
            ->method('publishFile')
            ->with($this->sourceDirWrite, $this->staticDirWrite, 'file.ext', 'some/file.ext')
            ->will($this->returnValue(true));

        $this->assertTrue($this->object->publish($this->getAsset()));
    }

    /**
     * Create an asset mock
     *
     * @return \Magento\Framework\View\Asset\File|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getAsset()
    {
        $asset = $this->createMock(\Magento\Framework\View\Asset\File::class);
        $asset->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('some/file.ext'));
        $asset->expects($this->any())
            ->method('getSourceFile')
            ->will($this->returnValue('/root/some/file.ext'));
        return $asset;
    }
}
