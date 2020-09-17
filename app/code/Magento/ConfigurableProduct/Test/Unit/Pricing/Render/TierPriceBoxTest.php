<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Pricing\Render;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolverInterface;
use Magento\Catalog\Pricing\Price\MinimalPriceCalculatorInterface;
use Magento\ConfigurableProduct\Pricing\Price\ConfigurableOptionsProviderInterface;
use Magento\ConfigurableProduct\Pricing\Render\TierPriceBox;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\PriceInfoInterface;
use Magento\Framework\Pricing\Render\RendererPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Msrp\Pricing\Price\MsrpPrice;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TierPriceBoxTest extends TestCase
{
    /**
     * @var Context|MockObject
     */
    private $context;

    /**
     * @var Product|MockObject
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
     * @var SalableResolverInterface|MockObject
     */
    private $salableResolver;

    /**
     * @var MinimalPriceCalculatorInterface|MockObject
     */
    private $minimalPriceCalculator;

    /**
     * @var ConfigurableOptionsProviderInterface|MockObject
     */
    private $configurableOptionsProvider;

    /**
     * @var TierPriceBox
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->context = $this->createPartialMock(Context::class, []);
        $this->saleableItem = $this->createPartialMock(Product::class, ['getPriceInfo']);
        $this->price = $this->getMockForAbstractClass(PriceInterface::class);
        $this->rendererPool = $this->createPartialMock(RendererPool::class, []);
        $this->salableResolver = $this->createPartialMock(SalableResolverInterface::class, ['isSalable']);
        $this->minimalPriceCalculator = $this->getMockForAbstractClass(MinimalPriceCalculatorInterface::class);
        $this->configurableOptionsProvider = $this->getMockForAbstractClass(
            ConfigurableOptionsProviderInterface::class
        );

        $this->model = (new ObjectManager($this))->getObject(
            TierPriceBox::class,
            [
                'context' => $this->context,
                'saleableItem' => $this->saleableItem,
                'price' => $this->price,
                'rendererPool' => $this->rendererPool,
                'salableResolver' => $this->salableResolver,
                'minimalPriceCalculator' => $this->minimalPriceCalculator,
                'configurableOptionsProvider' => $this->configurableOptionsProvider,
            ]
        );
    }

    public function testToHtmlEmptyWhenMsrpPriceIsApplicable(): void
    {
        $msrpPriceMock = $this->createPartialMock(
            MsrpPrice::class,
            ['canApplyMsrp', 'isMinimalPriceLessMsrp']
        );
        $msrpPriceMock->expects($this->once())
            ->method('canApplyMsrp')
            ->willReturn(true);
        $msrpPriceMock->expects($this->once())
            ->method('isMinimalPriceLessMsrp')
            ->willReturn(true);

        $priceInfoMock = $this->getMockForAbstractClass(PriceInfoInterface::class);
        $priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->willReturn($msrpPriceMock);

        $this->saleableItem->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($priceInfoMock);

        $result = $this->model->toHtml();
        $this->assertSame('', $result);
    }
}
