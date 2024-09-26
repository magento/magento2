<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Pricing\Render;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolverInterface;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Catalog\Pricing\Price\MinimalPriceCalculatorInterface;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\Catalog\Pricing\Price\SpecialPriceBulkResolver;
use Magento\ConfigurableProduct\Pricing\Price\ConfigurableOptionsProviderInterface;
use Magento\ConfigurableProduct\Pricing\Render\FinalPriceBox;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\PriceInfoInterface;
use Magento\Framework\Pricing\Render\RendererPool;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FinalPriceBoxTest extends TestCase
{
    /**
     * @var Context|MockObject
     */
    private Context $context;

    /**
     * @var Product|MockObject
     */
    private Product $saleableItem;

    /**
     * @var PriceInterface|MockObject
     */
    private PriceInterface $price;

    /**
     * @var RendererPool|MockObject
     */
    private RendererPool $rendererPool;

    /**
     * @var SalableResolverInterface|MockObject
     */
    private SalableResolverInterface $salableResolver;

    /**
     * @var MinimalPriceCalculatorInterface|MockObject
     */
    private MinimalPriceCalculatorInterface $minimalPriceCalculator;

    /**
     * @var ConfigurableOptionsProviderInterface|MockObject
     */
    private ConfigurableOptionsProviderInterface $configurableOptionsProvider;

    /**
     * @var FinalPriceBox
     */
    private FinalPriceBox $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->context = $this->createMock(Context::class);
        $this->saleableItem = $this->createMock(Product::class);
        $this->price = $this->getMockForAbstractClass(PriceInterface::class);
        $this->rendererPool = $this->createMock(RendererPool::class);
        $this->salableResolver = $this->getMockForAbstractClass(SalableResolverInterface::class);
        $this->minimalPriceCalculator = $this->getMockForAbstractClass(MinimalPriceCalculatorInterface::class);
        $this->configurableOptionsProvider = $this->getMockForAbstractClass(
            ConfigurableOptionsProviderInterface::class
        );

        $this->model = new FinalPriceBox(
            $this->context,
            $this->saleableItem,
            $this->price,
            $this->rendererPool,
            $this->salableResolver,
            $this->minimalPriceCalculator,
            $this->configurableOptionsProvider,
            []
        );
    }

    /**
     * @param float $regularPrice
     * @param float $finalPrice
     * @param bool $expected
     * @dataProvider hasSpecialPriceDataProvider
     * @throws \Exception
     */
    public function testHasSpecialPriceProductDetailsPage(
        float $regularPrice,
        float $finalPrice,
        bool  $expected
    ): void {
        $priceMockOne = $this->getMockForAbstractClass(PriceInterface::class);
        $priceMockOne->expects($this->once())
            ->method('getValue')
            ->willReturn($regularPrice);
        $priceMockTwo = $this->getMockForAbstractClass(PriceInterface::class);
        $priceMockTwo->expects($this->once())
            ->method('getValue')
            ->willReturn($finalPrice);
        $priceInfoMock = $this->getMockForAbstractClass(PriceInfoInterface::class);
        $priceInfoMock->expects($this->exactly(2))
            ->method('getPrice')
            ->willReturnMap(
                [
                    [RegularPrice::PRICE_CODE, $priceMockOne],
                    [FinalPrice::PRICE_CODE, $priceMockTwo],
                ]
            );

        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->exactly(2))
            ->method('getPriceInfo')
            ->willReturn($priceInfoMock);
        $this->configurableOptionsProvider->expects($this->once())
            ->method('getProducts')
            ->with($this->saleableItem)
            ->willReturn([$productMock]);

        $this->model->setData('is_product_list', false);
        $this->assertEquals($expected, $this->model->hasSpecialPrice());
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testHasSpecialPriceProductListingPage(): void
    {
        $productId = 1;
        $this->model->setData('is_product_list', true);
        $this->model->setData('special_price_map', [1 => true]);
        $this->saleableItem->expects($this->once())->method('getId')->willReturn($productId);

        $this->assertTrue($this->model->hasSpecialPrice());
    }

    /**
     * @return array
     */
    public static function hasSpecialPriceDataProvider(): array
    {
        return [
            [10., 20., false],
            [10., 10., false],
            [20., 10., true],
        ];
    }
}
