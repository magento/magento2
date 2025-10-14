<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);


namespace Magento\Wishlist\Test\Unit\DataProvider\Product\Collector;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductRender\ButtonInterface;
use Magento\Catalog\Api\Data\ProductRender\ButtonInterfaceFactory;
use Magento\Catalog\Api\Data\ProductRenderExtensionFactory;
use Magento\Catalog\Api\Data\ProductRenderExtensionInterface;
use Magento\Catalog\Api\Data\ProductRenderInterface;
use Magento\Wishlist\Helper\Data;
use Magento\Wishlist\Ui\DataProvider\Product\Collector\Button;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Collect information needed to render wishlist button on front
 */
class ButtonTest extends TestCase
{
    /** @var Button */
    private $button;

    /** @var ProductRenderExtensionFactory|MockObject */
    private $productRenderExtensionFactoryMock;

    /** @var Data|MockObject */
    private $wishlistHelperMock;

    /** @var ButtonInterfaceFactory|MockObject */
    private $buttonInterfaceFactoryMock;

    protected function setUp(): void
    {
        $this->productRenderExtensionFactoryMock = $this->getMockBuilder(ProductRenderExtensionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->buttonInterfaceFactoryMock = $this->getMockBuilder(ButtonInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->wishlistHelperMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->button = new Button(
            $this->wishlistHelperMock,
            $this->productRenderExtensionFactoryMock,
            $this->buttonInterfaceFactoryMock
        );
    }

    public function testCollect()
    {
        $productRendererMock = $this->getMockBuilder(ProductRenderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $productMock = $this->getMockBuilder(ProductInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $productRendererExtensionMock = $this->getMockBuilder(ProductRenderExtensionInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['setWishlistButton'])
            ->getMockForAbstractClass();
        $buttonInterfaceMock = $this->getMockBuilder(ButtonInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $productRendererMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($productRendererExtensionMock);
        $this->buttonInterfaceFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($buttonInterfaceMock);
        $this->wishlistHelperMock->expects($this->once())
            ->method('getAddParams')
            ->with($productMock)
            ->willReturn('http://www.example.com/');
        $buttonInterfaceMock->expects($this->once())
            ->method('setUrl')
            ->with('http://www.example.com/');
        $productRendererExtensionMock->expects($this->once())
            ->method('setWishlistButton')
            ->with($buttonInterfaceMock);

        $this->button->collect($productMock, $productRendererMock);
    }

    public function testCollectEmptyExtensionAttributes()
    {
        $productRendererMock = $this->getMockBuilder(ProductRenderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $productMock = $this->getMockBuilder(ProductInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $buttonInterfaceMock = $this->getMockBuilder(ButtonInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $productRendererExtensionMock = $this->getMockBuilder(ProductRenderExtensionInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['setWishlistButton'])
            ->getMockForAbstractClass();

        $productRendererMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn('');
        $this->productRenderExtensionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($productRendererExtensionMock);
        $this->buttonInterfaceFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($buttonInterfaceMock);
        $this->wishlistHelperMock->expects($this->once())
            ->method('getAddParams')
            ->with($productMock)
            ->willReturn('http://www.example.com/');
        $buttonInterfaceMock->expects($this->once())
            ->method('setUrl')
            ->with('http://www.example.com/');
        $productRendererExtensionMock->expects($this->once())
            ->method('setWishlistButton')
            ->with($buttonInterfaceMock);

        $this->button->collect($productMock, $productRendererMock);
    }
}
