<?php
/**
 * Copyright © Magento, Inc. All rights reserved. 
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Listing\Collector;

use Magento\Catalog\Api\Data\ProductRenderInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Ui\DataProvider\Product\Listing\Collector\Image;
use Magento\Framework\View\DesignInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\Data\ProductRender\ImageInterfaceFactory;
use Magento\Catalog\Api\Data\ProductRender\ImageInterface;

class ImageTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Magento\Catalog\Helper\ImageFactory | \PHPUnit_Framework_MockObject_MockObject */
    private $imageFactory;

    /** @var  \Magento\Framework\App\State | \PHPUnit_Framework_MockObject_MockObject */
    private $state;

    /** @var  StoreManagerInterface | \PHPUnit_Framework_MockObject_MockObject */
    private $storeManager;

    /** @var  DesignInterface | \PHPUnit_Framework_MockObject_MockObject */
    private $design;

    /** @var  Image */
    private $model;

    /** @var array  */
    private $imageCodes = ['widget_recently_viewed'];

    /** @var \Magento\Catalog\Api\Data\ProductRender\ImageInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $imageInterfaceFactory;

    public function setUp()
    {
        $this->imageFactory = $this->getMockBuilder(\Magento\Catalog\Helper\ImageFactory::class)
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
        $imageHelper = $this->getMockBuilder(\Magento\Catalog\Helper\Image::class)
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
                [$product, $imageCode, 1]
            )
            ->willReturn($imageHelper);
        $imageHelper->expects($this->once())
            ->method('getUrl')
            ->willReturn('url');
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
            ->method('setUrl')
            ->with();
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
}
