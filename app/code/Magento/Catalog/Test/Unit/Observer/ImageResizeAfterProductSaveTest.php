<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Observer;

use Magento\Catalog\Observer\ImageResizeAfterProductSave;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\App\State;
use Magento\MediaStorage\Service\ImageResize;
use Magento\MediaStorage\Service\ImageResizeScheduler;
use Magento\Catalog\Model\Config\CatalogMediaConfig;
use Magento\Framework\DataObject;

class ImageResizeAfterProductSaveTest extends TestCase
{
    /**
     * @var Observer|MockObject
     */
    private $observerMock;

    /**
     * @var Event|MockObject
     */
    private $eventMock;

    /**
     * @var Product|MockObject
     */
    private $productMock;

    /**
     * @var State|MockObject
     */
    private $stateMock;

    /**
     * @var CatalogMediaConfig|MockObject
     */
    private $catalogMediaConfigMock;

    /**
     * @var ImageResizeScheduler|MockObject
     */
    private $imageResizeSchedulerMock;

    /**
     * @var ImageResize|MockObject
     */
    private $imageResizeMock;

    /**
     * @var string
     */
    private $imagePath;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->imagePath = 'path/to/image.jpg';
        $images = [new DataObject(['file' => $this->imagePath])];
        $this->observerMock = $this->createMock(Observer::class);
        $this->eventMock = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->addMethods(['getProduct'])
            ->getMock();
        $this->productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId', 'getMediaGalleryImages'])
            ->getMock();
        $this->stateMock = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isAreaCodeEmulated'])
            ->getMock();
        $this->catalogMediaConfigMock = $this->getMockBuilder(CatalogMediaConfig::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getMediaUrlFormat'])
            ->getMock();
        $this->imageResizeSchedulerMock = $this->getMockBuilder(ImageResizeScheduler::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['schedule'])
            ->getMock();
        $this->imageResizeMock = $this->getMockBuilder(ImageResize::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['resizeFromImageName'])
            ->getMock();

        $this->observerMock
            ->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventMock);
        $this->eventMock
            ->expects($this->once())
            ->method('getProduct')
            ->willReturn($this->productMock);
        $this->productMock
            ->expects($this->any())
            ->method('getId')
            ->willReturn(null);
        $this->productMock
            ->expects($this->once())
            ->method('getMediaGalleryImages')
            ->willReturn($images);
    }

    /**
     * Test observer execute method when ImageResizeScheduler is called
     */
    public function testExecuteImageResizeScheduler(): void
    {
        $observer = new ImageResizeAfterProductSave(
            $this->imageResizeMock,
            $this->stateMock,
            $this->catalogMediaConfigMock,
            $this->imageResizeSchedulerMock,
            true
        );
        $this->imageResizeMock
            ->expects($this->never())
            ->method('resizeFromImageName');
        $this->imageResizeSchedulerMock
            ->expects($this->once())
            ->method('schedule')
            ->with($this->imagePath);
        $observer->execute($this->observerMock);
    }

    /**
     * Test observer execute method when ImageResize is called
     */
    public function testExecuteImageResize(): void
    {
        $observer = new ImageResizeAfterProductSave(
            $this->imageResizeMock,
            $this->stateMock,
            $this->catalogMediaConfigMock,
            $this->imageResizeSchedulerMock,
            false
        );
        $this->imageResizeMock
            ->expects($this->once())
            ->method('resizeFromImageName')
            ->with($this->imagePath);
        $this->imageResizeSchedulerMock
            ->expects($this->never())
            ->method('schedule');
        $observer->execute($this->observerMock);
    }
}
