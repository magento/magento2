<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Test\Unit\Block\Customer;

use Magento\Framework\Pricing\Render;
use Magento\Wishlist\Block\Customer\Sidebar;

class SidebarTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Block\Product\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productContext;

    /**
     * @var \Magento\Framework\App\Http\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $httpContext;

    /**
     * @var Sidebar
     */
    private $block;

    /**
     * @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $layout;

    protected function setUp()
    {
        $this->layout = $this->getMockBuilder('Magento\Framework\View\LayoutInterface')
            ->getMockForAbstractClass();

        $this->productContext = $this->getMockBuilder('Magento\Catalog\Block\Product\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->productContext->expects($this->any())
            ->method('getLayout')
            ->willReturn($this->layout);

        $this->httpContext = $this->getMockBuilder('Magento\Framework\App\Http\Context')
            ->disableOriginalConstructor()
            ->getMock();

        $this->block = new Sidebar(
            $this->productContext,
            $this->httpContext
        );
    }

    public function testGetProductPriceHtml()
    {
        $priceType = 'wishlist_configured_price';
        $expected = 'block content';

        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $renderMock = $this->getMockBuilder('\Magento\Framework\Pricing\Render')
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

        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $renderMock = $this->getMockBuilder('\Magento\Framework\Pricing\Render')
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
                'Magento\Framework\Pricing\Render',
                'product.price.render.default',
                ['data' => ['price_render_handle' => 'catalog_product_prices']]
            )
            ->willReturn($renderMock);

        $result = $this->block->getProductPriceHtml($productMock, $priceType, Render::ZONE_ITEM_LIST);
        $this->assertEquals($expected, $result);
    }
}
