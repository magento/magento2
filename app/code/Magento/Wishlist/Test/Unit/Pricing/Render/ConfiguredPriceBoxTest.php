<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Test\Unit\Pricing\Render;

use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolverInterface;
use Magento\Catalog\Pricing\Price\ConfiguredPrice;
use Magento\Catalog\Pricing\Price\ConfiguredPriceSelection;
use Magento\Catalog\Pricing\Price\MinimalPriceCalculatorInterface;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\Render\RendererPool;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\LayoutInterface;
use Magento\SharedCatalog\Model\Form\Storage\PriceCalculator;
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

    /**
     * @var SalableResolverInterface|MockObject
     */
    private $salableResolverMock;

    /**
     * @var MinimalPriceCalculatorInterface|MockObject
     */
    private $priceCalculatorMock;

    /**
     * @var ConfiguredPriceSelection|MockObject
     */
    private $configuredPriceMock;

    protected function setUp(): void
    {
        $this->templateContext = $this->createMock(Context::class);

        $this->saleableItem = $this->createMock(SaleableInterface::class);

        $this->price = $this->createMock(ConfiguredPrice::class);

        $this->rendererPool = $this->createMock(RendererPool::class);

        $this->item = $this->createMock(ItemInterface::class);

        $this->salableResolverMock = $this->createMock(SalableResolverInterface::class);

        $this->priceCalculatorMock = $this->createMock(MinimalPriceCalculatorInterface::class);

        $this->configuredPriceMock = $this->createMock(ConfiguredPriceSelection::class);

        $this->model = new ConfiguredPriceBox(
            $this->templateContext,
            $this->saleableItem,
            $this->price,
            $this->rendererPool,
            ['item' => $this->item],
            $this->salableResolverMock,
            $this->priceCalculatorMock,
            $this->configuredPriceMock
        );
    }

    public function testSetLayout()
    {
        $layoutMock = $this->createMock(LayoutInterface::class);

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
