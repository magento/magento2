<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Listing\Collector;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductRender\ImageInterface;
use Magento\Catalog\Api\Data\ProductRender\ImageInterfaceFactory;
use Magento\Catalog\Api\Data\ProductRenderInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Helper\ImageFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Ui\DataProvider\Product\Listing\Collector\Image;
use Magento\Framework\App\State;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\DesignLoader;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ImageTest extends TestCase
{
    /** @var ImageFactory|MockObject */
    private $imageFactory;

    /** @var  State|MockObject */
    private $state;

    /** @var  StoreManagerInterface|MockObject */
    private $storeManager;

    /** @var  DesignInterface|MockObject */
    private $design;

    /** @var DesignLoader|MockObject*/
    private $designLoader;

    /** @var  Image */
    private $model;

    /** @var array */
    private $imageCodes = ['widget_recently_viewed'];

    /** @var ImageInterfaceFactory|MockObject */
    private $imageInterfaceFactory;

    protected function setUp(): void
    {
        $this->imageFactory = $this->createMock(ImageFactory::class);

        $this->imageInterfaceFactory = $this->createPartialMock(
            ImageInterfaceFactory::class,
            ['create']
        );

        $this->state = $this->createMock(State::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->design = $this->createMock(DesignInterface::class);
        $this->designLoader = $this->createMock(DesignLoader::class);
        $this->model = new Image(
            $this->imageFactory,
            $this->state,
            $this->storeManager,
            $this->design,
            $this->imageInterfaceFactory,
            $this->imageCodes,
            $this->designLoader
        );
    }

    public function testGet()
    {
        $product = $this->createMock(Product::class);
        $image = $this->createMock(ImageInterface::class);

        $imageCode = 'widget_recently_viewed';
        $productRenderInfoDto = $this->createMock(ProductRenderInterface::class);

        $productRenderInfoDto->expects($this->once())
            ->method('getStoreId')
            ->willReturn('1');
        $imageHelper = $this->createMock(ImageHelper::class);
        $this->imageInterfaceFactory
            ->method('create')->willReturn($image);

        $this->state->expects($this->once())
            ->method('emulateAreaCode')
            ->with(
                'frontend',
                [$this->model, "emulateImageCreating"],
                [$product, $imageCode, 1, $image]
            )
            ->willReturn($imageHelper);

        $width = 5;
        $height = 10;
        $imageHelper->expects($this->once())
            ->method('getHeight')
            ->willReturn($height);
        $imageHelper->expects($this->once())
            ->method('getWidth')
            ->willReturn($width);
        $imageHelper->expects($this->once())
            ->method('getLabel')
            ->willReturn('Label');

        $image->expects($this->once())
            ->method('setCode')
            ->with();
        $image->expects($this->once())
            ->method('setWidth')
            ->with();
        $image->expects($this->once())
            ->method('setLabel')
            ->with();
        $image->expects($this->once())
            ->method('setResizedHeight')
            ->with($height);
        $image->expects($this->once())
            ->method('setResizedWidth')
            ->with($width);

        $productRenderInfoDto->expects($this->once())
            ->method('setImages')
            ->with(
                [
                    $image
                ]
            );
        $this->model->collect($product, $productRenderInfoDto);
    }

    public function testEmulateImageCreating()
    {
        $productMock = $this->createMock(ProductInterface::class);
        $imageMock = $this->createMock(ImageInterface::class);
        $imageHelperMock = $this->createMock(ImageHelper::class);
        $this->imageFactory->expects($this->once())
            ->method('create')
            ->willReturn($imageHelperMock);

        $imageHelperMock->expects($this->once())
            ->method('init')
            ->with($productMock, 'widget_recently_viewed');
        $imageHelperMock->expects($this->once())
            ->method('getUrl')
            ->willReturn('url');
        $imageMock->expects($this->once())
            ->method('setUrl')
            ->with('url');

        $this->assertEquals(
            $imageHelperMock,
            $this->model->emulateImageCreating($productMock, 'widget_recently_viewed', 1, $imageMock)
        );
    }
}
