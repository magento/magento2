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
        $this->observerMock = $this->createMock(Observer::class);
        $this->eventMock = $this->createPartialMockWithReflection(
            Event::class,
            ['getProduct']
        );
        $this->productMock = $this->createPartialMock(
            Product::class,
            ['getId', 'getMediaGalleryImages', 'getData', 'getOrigData']
        );
        $this->stateMock = $this->createPartialMock(State::class, ['isAreaCodeEmulated']);
        $this->catalogMediaConfigMock = $this->createPartialMock(CatalogMediaConfig::class, ['getMediaUrlFormat']);
        $this->imageResizeSchedulerMock = $this->createPartialMock(ImageResizeScheduler::class, ['schedule']);
        $this->imageResizeMock = $this->createPartialMock(ImageResize::class, ['resizeFromImageName']);

        $this->observerMock
            ->method('getEvent')
            ->willReturn($this->eventMock);
        $this->eventMock
            ->method('getProduct')
            ->willReturn($this->productMock);
    }

    /**
     * Test observer execute method when ImageResizeScheduler is called
     */
    public function testExecuteImageResizeScheduler(): void
    {
        $this->setUpNewProduct();
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
        $this->setUpNewProduct();
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

    /**
     * Images flagged as removed before saving an existing product must not be scheduled for resize.
     *
     * @see https://github.com/magento/magento2/issues/39146
     */
    public function testExecuteSkipsImagesFlaggedAsRemoved(): void
    {
        $existingImage = 'path/to/existing-image.jpg';
        $removedImage = 'path/to/removed-image.tmp';
        $newImage = 'path/to/new-image.jpg';

        $this->productMock
            ->method('getId')
            ->willReturn(1);
        $this->productMock
            ->expects($this->never())
            ->method('getMediaGalleryImages');
        $this->productMock
            ->method('getData')
            ->with('media_gallery')
            ->willReturn([
                'images' => [
                    ['file' => $existingImage],
                    ['file' => $removedImage, 'removed' => '1'],
                    ['file' => $newImage],
                ],
            ]);
        $this->productMock
            ->method('getOrigData')
            ->with('media_gallery')
            ->willReturn([
                'images' => [
                    ['file' => $existingImage],
                ],
            ]);

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
            ->with($newImage);
        $observer->execute($this->observerMock);
    }

    /**
     * Configure the product mock for the new-product code path.
     */
    private function setUpNewProduct(): void
    {
        $images = [new DataObject(['file' => $this->imagePath])];
        $this->productMock
            ->method('getId')
            ->willReturn(null);
        $this->productMock
            ->expects($this->once())
            ->method('getMediaGalleryImages')
            ->willReturn($images);
    }
}
