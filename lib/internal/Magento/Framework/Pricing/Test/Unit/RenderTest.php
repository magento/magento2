<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Pricing\Test\Unit;

use \Magento\Framework\Pricing\Render;

/**
 * Test class for \Magento\Framework\Pricing\Render
 */
class RenderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Render
     */
    protected $model;

    /**
     * @var Render\Layout|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $priceLayout;

    /**
     * @var \Magento\Framework\Pricing\Price\PriceInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $price;

    /**
     * @var \Magento\Framework\Pricing\Amount\Base|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $amount;

    /**
     * @var \Magento\Framework\Pricing\SaleableInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $saleableItem;

    /**
     * @var \Magento\Framework\Pricing\Render\RendererPool|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $renderPool;

    protected function setUp(): void
    {
        $this->priceLayout = $this->getMockBuilder(\Magento\Framework\Pricing\Render\Layout::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->price = $this->getMockBuilder(\Magento\Framework\Pricing\Price\PriceInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->amount = $this->getMockBuilder(\Magento\Framework\Pricing\Amount\Base::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->saleableItem = $this->getMockBuilder(\Magento\Framework\Pricing\SaleableInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->renderPool = $this->getMockBuilder(\Magento\Framework\Pricing\Render\RendererPool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Framework\Pricing\Render::class,
            [
                'priceLayout' => $this->priceLayout
            ]
        );
    }

    public function testSetLayout()
    {
        $priceRenderHandle = 'price_render_handle';

        $this->priceLayout->expects($this->once())
            ->method('addHandle')
            ->with($priceRenderHandle);

        $this->priceLayout->expects($this->once())
            ->method('loadLayout');

        $layout = $this->createMock(\Magento\Framework\View\LayoutInterface::class);
        $this->model->setPriceRenderHandle($priceRenderHandle);
        $this->model->setLayout($layout);
    }

    /**
     */
    public function testRenderWithoutRenderList()
    {
        $this->expectException(\RuntimeException::class);

        $priceType = 'final';
        $arguments = ['param' => 1];
        $result = '';

        $this->priceLayout->expects($this->once())
            ->method('getBlock')
            ->with('render.product.prices')
            ->willReturn(false);

        $this->assertEquals($result, $this->model->render($priceType, $this->saleableItem, $arguments));
    }

    public function testRender()
    {
        $priceType = 'final';
        $arguments = ['param' => 1];
        $result = 'simple.final';

        $pricingRender = $this->createMock(\Magento\Framework\Pricing\Render::class);
        $this->renderPool->expects($this->once())
            ->method('createPriceRender')
            ->willReturn($pricingRender);
        $pricingRender->expects($this->once())
            ->method('toHtml')
            ->willReturn('simple.final');
        $this->priceLayout->expects($this->once())
            ->method('getBlock')
            ->with('render.product.prices')
            ->willReturn($this->renderPool);
        $this->assertEquals($result, $this->model->render($priceType, $this->saleableItem, $arguments));
    }

    public function testRenderDefault()
    {
        $priceType = 'special';
        $arguments = ['param' => 15];
        $result = 'default.special';
        $pricingRender = $this->createMock(\Magento\Framework\Pricing\Render::class);
        $this->renderPool->expects($this->once())
            ->method('createPriceRender')
            ->willReturn($pricingRender);
        $pricingRender->expects($this->once())
            ->method('toHtml')
            ->willReturn('default.special');
        $this->priceLayout->expects($this->once())
            ->method('getBlock')
            ->with('render.product.prices')
            ->willReturn($this->renderPool);

        $this->assertEquals($result, $this->model->render($priceType, $this->saleableItem, $arguments));
    }

    public function testRenderDefaultDefault()
    {
        $priceType = 'final';
        $arguments = ['param' => 15];
        $result = 'default.default';

        $pricingRender = $this->createMock(\Magento\Framework\Pricing\Render::class);
        $this->renderPool->expects($this->once())
            ->method('createPriceRender')
            ->willReturn($pricingRender);
        $pricingRender->expects($this->once())
            ->method('toHtml')
            ->willReturn('default.default');
        $this->priceLayout->expects($this->once())
            ->method('getBlock')
            ->with('render.product.prices')
            ->willReturn($this->renderPool);

        $this->assertEquals($result, $this->model->render($priceType, $this->saleableItem, $arguments));
    }

    public function testAmountRender()
    {
        $arguments = ['param' => 15];
        $expectedResult = 'default.default';

        $pricingRender = $this->createMock(
            \Magento\Framework\Pricing\Render\Amount::class
        );
        $this->renderPool->expects($this->once())
            ->method('createAmountRender')
            ->with(
                $this->equalTo($this->amount),
                $this->equalTo($this->saleableItem),
                $this->equalTo($this->price),
                $this->equalTo($arguments)
            )
            ->willReturn($pricingRender);
        $pricingRender->expects($this->once())
            ->method('toHtml')
            ->willReturn('default.default');
        $this->priceLayout->expects($this->once())
            ->method('getBlock')
            ->with('render.product.prices')
            ->willReturn($this->renderPool);

        $result = $this->model->renderAmount($this->amount, $this->price, $this->saleableItem, $arguments);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     */
    public function testAmountRenderNoRenderPool()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Wrong Price Rendering layout configuration. Factory block is missed');

        $this->priceLayout->expects($this->once())
            ->method('getBlock')
            ->with('render.product.prices')
            ->willReturn(false);

        $this->model->renderAmount($this->amount, $this->price, $this->saleableItem);
    }
}
