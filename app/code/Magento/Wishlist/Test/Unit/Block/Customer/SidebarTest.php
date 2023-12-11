<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Test\Unit\Block\Customer;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\Render;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\LayoutInterface;
use Magento\Wishlist\Block\Customer\Sidebar;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SidebarTest extends TestCase
{
    /**
     * @var Context|MockObject
     */
    private $productContext;

    /**
     * @var \Magento\Framework\App\Http\Context|MockObject
     */
    private $httpContext;

    /**
     * @var Sidebar
     */
    private $block;

    /**
     * @var LayoutInterface|MockObject
     */
    private $layout;

    protected function setUp(): void
    {
        $this->layout = $this->getMockBuilder(LayoutInterface::class)
            ->getMockForAbstractClass();

        $this->productContext = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productContext->expects($this->any())
            ->method('getLayout')
            ->willReturn($this->layout);

        $this->httpContext = $this->getMockBuilder(\Magento\Framework\App\Http\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);

        $this->block = $objectManager->getObject(
            Sidebar::class,
            [
                'context' => $this->productContext,
                'httpContext' => $this->httpContext
            ]
        );
    }

    public function testGetProductPriceHtml()
    {
        $priceType = 'wishlist_configured_price';
        $expected = 'block content';

        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $renderMock = $this->getMockBuilder(Render::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderMock->expects($this->once())
            ->method('render')
            ->with($priceType, $productMock, ['zone' => Render::ZONE_ITEM_LIST])
            ->willReturn($expected);

        $this->layout->expects($this->once())
            ->method('getBlock')
            ->with('product.price.render.default')
            ->willReturn($renderMock);
        $this->layout->expects($this->never())
            ->method('createBlock');

        $result = $this->block->getProductPriceHtml($productMock, $priceType, Render::ZONE_ITEM_LIST);
        $this->assertEquals($expected, $result);
    }

    public function testGetProductPriceHtmlCreateBlock()
    {
        $priceType = 'wishlist_configured_price';
        $expected = 'block content';

        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $renderMock = $this->getMockBuilder(Render::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderMock->expects($this->once())
            ->method('render')
            ->with($priceType, $productMock, ['zone' => Render::ZONE_ITEM_LIST])
            ->willReturn($expected);

        $this->layout->expects($this->once())
            ->method('getBlock')
            ->with('product.price.render.default')
            ->willReturn(false);
        $this->layout->expects($this->once())
            ->method('createBlock')
            ->with(
                Render::class,
                'product.price.render.default',
                ['data' => ['price_render_handle' => 'catalog_product_prices']]
            )
            ->willReturn($renderMock);

        $result = $this->block->getProductPriceHtml($productMock, $priceType, Render::ZONE_ITEM_LIST);
        $this->assertEquals($expected, $result);
    }
}
