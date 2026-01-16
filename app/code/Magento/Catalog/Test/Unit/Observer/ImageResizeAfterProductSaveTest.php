<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Observer;

use Magento\Catalog\Observer\ImageResizeAfterProductSave;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\App\State;
use Magento\MediaStorage\Service\ImageResize;
use Magento\MediaStorage\Service\ImageResizeScheduler;
use Magento\Catalog\Model\Config\CatalogMediaConfig;
use Magento\Framework\DataObject;

class ImageResizeAfterProductSaveTest extends TestCase
{
    use MockCreationTrait;

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
        $this->eventMock = $this->createPartialMockWithReflection(
            Event::class,
            ['getProduct']
        );
        $this->productMock = $this->createPartialMock(Product::class, ['getId', 'getMediaGalleryImages']);
        $this->stateMock = $this->createPartialMock(State::class, ['isAreaCodeEmulated']);
        $this->catalogMediaConfigMock = $this->createPartialMock(CatalogMediaConfig::class, ['getMediaUrlFormat']);
        $this->imageResizeSchedulerMock = $this->createPartialMock(ImageResizeScheduler::class, ['schedule']);
        $this->imageResizeMock = $this->createPartialMock(ImageResize::class, ['resizeFromImageName']);

        $this->observerMock
            ->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventMock);
        $this->eventMock
            ->expects($this->once())
            ->method('getProduct')
            ->willReturn($this->productMock);
        $this->productMock
            ->method('getId')->willReturn(null);
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
