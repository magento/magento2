<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Listing\Collector;

use Magento\Catalog\Api\Data\ProductRenderInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Ui\DataProvider\Product\Listing\Collector\Image;
use Magento\Framework\View\DesignInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Helper\ImageFactory;
use Magento\Catalog\Api\Data\ProductRender\ImageInterface;
use Magento\Catalog\Helper\Image as ImageHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ImageTest extends \PHPUnit_Framework_TestCase
{
    /** @var ImageFactory | \PHPUnit_Framework_MockObject_MockObject */
    private $imageFactory;

    /** @var  \Magento\Framework\App\State | \PHPUnit_Framework_MockObject_MockObject */
    private $state;

    /** @var  StoreManagerInterface | \PHPUnit_Framework_MockObject_MockObject */
    private $storeManager;

    /** @var  DesignInterface | \PHPUnit_Framework_MockObject_MockObject */
    private $design;

    /** @var  Image */
    private $model;

    /** @var array */
    private $imageCodes = ['widget_recently_viewed'];

    /** @var \Magento\Catalog\Api\Data\ProductRender\ImageInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $imageInterfaceFactory;

    public function setUp()
    {
        $this->imageFactory = $this->getMockBuilder(ImageFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->imageInterfaceFactory = $this->getMockBuilder(
            \Magento\Catalog\Api\Data\ProductRender\ImageInterfaceFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->state = $this->getMockBuilder(\Magento\Framework\App\State::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMock(StoreManagerInterface::class);
        $this->design = $this->getMock(DesignInterface::class);
        $this->model = new Image(
            $this->imageFactory,
            $this->state,
            $this->storeManager,
            $this->design,
            $this->imageInterfaceFactory,
            $this->imageCodes
        );
    }

    public function testGet()
    {
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $image = $this->getMockBuilder(ImageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $imageCode = 'widget_recently_viewed';
        $productRenderInfoDto = $this->getMock(ProductRenderInterface::class);

        $productRenderInfoDto->expects($this->once())
            ->method('getStoreId')
            ->willReturn('1');
        $imageHelper = $this->getMockBuilder(ImageHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->imageInterfaceFactory
            ->expects($this->any())
            ->method('create')
            ->willReturn($image);

        $imageHelper->expects($this->once())
            ->method('getResizedImageInfo')
            ->willReturn([11, 11]);
        $this->state->expects($this->once())
            ->method('emulateAreaCode')
            ->with(
                'frontend',
                [$this->model, "emulateImageCreating"],
                [$product, $imageCode, 1, $image]
            )
            ->willReturn($imageHelper);

        $imageHelper->expects($this->once())
            ->method('getHeight')
            ->willReturn(10);
        $imageHelper->expects($this->once())
            ->method('getWidth')
            ->willReturn(10);
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
            ->with(11);
        $image->expects($this->once())
            ->method('setResizedWidth')
            ->with(11);

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
        $productMock = $this->getMockBuilder(ProductInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $imageMock = $this->getMockBuilder(ImageInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $imageHelperMock = $this->getMockBuilder(\Magento\Catalog\Helper\Image::class)
            ->disableOriginalConstructor()
            ->getMock();
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
