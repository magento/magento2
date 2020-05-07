<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Pricing\Test\Unit;

use Magento\Framework\Pricing\Amount\Base;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\Render;
use Magento\Framework\Pricing\Render\Amount;
use Magento\Framework\Pricing\Render\Layout;
use Magento\Framework\Pricing\Render\RendererPool;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Framework\Pricing\Render
 */
class RenderTest extends TestCase
{
    /**
     * @var Render
     */
    protected $model;

    /**
     * @var Render\Layout|MockObject
     */
    protected $priceLayout;

    /**
     * @var PriceInterface|MockObject
     */
    protected $price;

    /**
     * @var Base|MockObject
     */
    protected $amount;

    /**
     * @var SaleableInterface|MockObject
     */
    protected $saleableItem;

    /**
     * @var RendererPool|MockObject
     */
    protected $renderPool;

    protected function setUp(): void
    {
        $this->priceLayout = $this->getMockBuilder(Layout::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->price = $this->getMockBuilder(PriceInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->amount = $this->getMockBuilder(Base::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->saleableItem = $this->getMockBuilder(SaleableInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->renderPool = $this->getMockBuilder(RendererPool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            Render::class,
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

        $layout = $this->getMockForAbstractClass(LayoutInterface::class);
        $this->model->setPriceRenderHandle($priceRenderHandle);
        $this->model->setLayout($layout);
    }

    public function testRenderWithoutRenderList()
    {
        $this->expectException('RuntimeException');
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

        $pricingRender = $this->createMock(Render::class);
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
        $pricingRender = $this->createMock(Render::class);
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

        $pricingRender = $this->createMock(Render::class);
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
            Amount::class
        );
        $this->renderPool->expects($this->once())
            ->method('createAmountRender')
            ->with(
                $this->amount,
                $this->saleableItem,
                $this->price,
                $arguments
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

    public function testAmountRenderNoRenderPool()
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('Wrong Price Rendering layout configuration. Factory block is missed');
        $this->priceLayout->expects($this->once())
            ->method('getBlock')
            ->with('render.product.prices')
            ->willReturn(false);

        $this->model->renderAmount($this->amount, $this->price, $this->saleableItem);
    }
}
