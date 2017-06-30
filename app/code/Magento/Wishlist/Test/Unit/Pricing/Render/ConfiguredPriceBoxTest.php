<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Test\Unit\Pricing\Render;

use Magento\Wishlist\Pricing\Render\ConfiguredPriceBox;

class ConfiguredPriceBoxTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Element\Template\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $templateContext;

    /**
     * @var \Magento\Framework\Pricing\SaleableInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $saleableItem;

    /**
     * @var \Magento\Framework\Pricing\Price\PriceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $price;

    /**
     * @var \Magento\Framework\Pricing\Render\RendererPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $rendererPool;

    /**
     * @var ConfiguredPriceBox
     */
    private $model;

    /**
     * @var \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $item;

    protected function setUp()
    {
        $this->templateContext = $this->getMockBuilder(\Magento\Framework\View\Element\Template\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->saleableItem = $this->getMockBuilder(\Magento\Framework\Pricing\SaleableInterface::class)
            ->getMockForAbstractClass();

        $this->price = $this->getMockBuilder(\Magento\Framework\Pricing\Price\PriceInterface::class)
            ->setMethods(['setItem'])
            ->getMockForAbstractClass();

        $this->rendererPool = $this->getMockBuilder(\Magento\Framework\Pricing\Render\RendererPool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->item = $this->getMockBuilder(\Magento\Catalog\Model\Product\Configuration\Item\ItemInterface::class)
            ->getMockForAbstractClass();

        $this->model = new ConfiguredPriceBox(
            $this->templateContext,
            $this->saleableItem,
            $this->price,
            $this->rendererPool,
            ['item' => $this->item]
        );
    }

    public function testSetLayout()
    {
        $layoutMock = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)
            ->getMockForAbstractClass();

        $this->price->expects($this->once())
            ->method('setItem')
            ->with($this->item)
            ->willReturnSelf();

        $this->assertInstanceOf(
            \Magento\Wishlist\Pricing\Render\ConfiguredPriceBox::class,
            $this->model->setLayout($layoutMock)
        );
    }
}
