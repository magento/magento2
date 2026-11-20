<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\ViewModel\Product;

use Magento\Catalog\ViewModel\Product\Gallery;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GalleryTest extends TestCase
{
    /** @var ScopeConfigInterface|MockObject */
    private ScopeConfigInterface $scopeConfig;

    /** @var StoreManagerInterface|MockObject */
    private StoreManagerInterface $storeManager;

    /** @var \Magento\Catalog\Block\Product\View\Gallery|MockObject */
    private \Magento\Catalog\Block\Product\View\Gallery $block;

    /**
     * @var Gallery
     */
    private Gallery $galleryViewModel;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->block = $this->createMock(\Magento\Catalog\Block\Product\View\Gallery::class);

        $this->galleryViewModel = new Gallery(
            $this->scopeConfig,
            $this->storeManager,
            $this->block
        );
    }

    /**
     * @return void
     * @throws NoSuchEntityException
     */
    public function testGetMainProductImageReturnsMainImageMediumUrlWhenFound(): void
    {
        $image1 = $this->createImageMock('https://example.com/img1.jpg');
        $image2 = $this->createImageMock('https://example.com/img2.jpg');

        $galleryImages = $this->createGalleryImagesMock([$image1, $image2], $image1);

        $this->block->expects(self::once())
            ->method('getGalleryImages')
            ->willReturn($galleryImages);

        $this->block->method('isMainImage')
            ->willReturnCallback(function ($image) use ($image2) {
                return $image === $image2;
            });

        $galleryImages->expects(self::never())->method('getFirstItem');

        $this->scopeConfig->expects(self::once())
            ->method('isSetFlag')
            ->with(Store::XML_PATH_STORE_IN_URL)
            ->willReturn(false);

        $result = $this->galleryViewModel->getMainProductImage();

        self::assertSame('https://example.com/img2.jpg', $result);
    }

    /**
     * @return void
     * @throws NoSuchEntityException
     */
    public function testGetMainProductImageFallsBackToFirstItemWhenNoMainImageMatches(): void
    {
        $image1 = $this->createImageMock('https://example.com/img1.jpg');
        $image2 = $this->createImageMock('https://example.com/img2.jpg');

        $galleryImages = $this->createGalleryImagesMock([$image1, $image2], $image1);
        $this->block->expects(self::exactly(2))
            ->method('getGalleryImages')
            ->willReturn($galleryImages);

        $this->block->method('isMainImage')->willReturn(false);
        $galleryImages->expects(self::once())
            ->method('getFirstItem')
            ->willReturn($image1);

        $this->scopeConfig->expects(self::once())
            ->method('isSetFlag')
            ->with(Store::XML_PATH_STORE_IN_URL)
            ->willReturn(false);

        $result = $this->galleryViewModel->getMainProductImage();

        self::assertSame('https://example.com/img1.jpg', $result);
    }

    /**
     * @return void
     * @throws NoSuchEntityException
     */
    public function testGetMainProductImageAppendsStoreCodeWhenStoreInUrlEnabled(): void
    {
        $image = $this->createImageMock('https://example.com/img.jpg');
        $galleryImages = $this->createGalleryImagesMock([$image], $image);

        $this->block->expects(self::once())
            ->method('getGalleryImages')
            ->willReturn($galleryImages);

        $this->block->expects(self::once())
            ->method('isMainImage')
            ->with($image)
            ->willReturn(true);

        $this->scopeConfig->expects(self::once())
            ->method('isSetFlag')
            ->with(Store::XML_PATH_STORE_IN_URL)
            ->willReturn(true);

        $store = $this->createMock(StoreInterface::class);
        $store->expects(self::once())->method('getCode')->willReturn('secondview');

        $this->storeManager->expects(self::once())
            ->method('getStore')
            ->willReturn($store);

        $result = $this->galleryViewModel->getMainProductImage();

        self::assertSame('https://example.com/img.jpg?___store=secondview', $result);
    }

    /**
     * Create image mock object
     *
     * @param string $mediumUrl
     * @return DataObject|MockObject
     * @throws Exception
     */
    private function createImageMock(string $mediumUrl): DataObject|MockObject
    {
        $image = $this->createMock(DataObject::class);
        $image->method('getData')
            ->with('medium_image_url')
            ->willReturn($mediumUrl);

        return $image;
    }

    /**
     * Create image collection mock object
     *
     * @param array $items
     * @param DataObject|MockObject $firstItem
     * @return MockObject
     * @throws Exception
     */
    private function createGalleryImagesMock(array $items, DataObject|MockObject $firstItem): MockObject
    {
        $galleryImages = $this->createMock(Collection::class);
        $galleryImages->method('getItems')->willReturn($items);
        $galleryImages->method('getFirstItem')->willReturn($firstItem);

        return $galleryImages;
    }
}
