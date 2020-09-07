<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Test\Unit\Pricing\Render;

use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\Render\RendererPool;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\LayoutInterface;
use Magento\Wishlist\Pricing\Render\ConfiguredPriceBox;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfiguredPriceBoxTest extends TestCase
{
    /**
     * @var Context|MockObject
     */
    private $templateContext;

    /**
     * @var SaleableInterface|MockObject
     */
    private $saleableItem;

    /**
     * @var PriceInterface|MockObject
     */
    private $price;

    /**
     * @var RendererPool|MockObject
     */
    private $rendererPool;

    /**
     * @var ConfiguredPriceBox
     */
    private $model;

    /**
     * @var ItemInterface|MockObject
     */
    private $item;

    protected function setUp(): void
    {
        $this->templateContext = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->saleableItem = $this->getMockBuilder(SaleableInterface::class)
            ->getMockForAbstractClass();

        $this->price = $this->getMockBuilder(PriceInterface::class)
            ->setMethods(['setItem'])
            ->getMockForAbstractClass();

        $this->rendererPool = $this->getMockBuilder(RendererPool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->item = $this->getMockBuilder(ItemInterface::class)
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
        $layoutMock = $this->getMockBuilder(LayoutInterface::class)
            ->getMockForAbstractClass();

        $this->price->expects($this->once())
            ->method('setItem')
            ->with($this->item)
            ->willReturnSelf();

        $this->assertInstanceOf(
            ConfiguredPriceBox::class,
            $this->model->setLayout($layoutMock)
        );
    }
}
