<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Ui\DataProvider\Product\Listing\Collector;

use Magento\Catalog\Api\Data\ProductRender\PriceInfoExtensionInterface;
use Magento\Catalog\Api\Data\ProductRender\PriceInfoExtensionInterfaceFactory;
use Magento\Catalog\Api\Data\ProductRender\PriceInfoInterface;
use Magento\Catalog\Api\Data\ProductRender\PriceInfoInterfaceFactory;
use Magento\Catalog\Api\Data\ProductRenderInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRender\FormattedPriceInfoBuilder;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\PriceInfoInterface as FrameworkPriceInfoInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Tax\Ui\DataProvider\Product\Listing\Collector\Tax;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Tax collector
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TaxTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Tax
     */
    private Tax $model;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    private MockObject $priceCurrencyMock;

    /**
     * @var PriceInfoInterface|MockObject
     */
    private MockObject $renderPriceInfoMock;

    /**
     * @var PriceInfoInterfaceFactory|MockObject
     */
    private MockObject $priceInfoFactory;

    /**
     * @var PriceInfoExtensionInterface|MockObject
     */
    private MockObject $extensionAttributes;

    /**
     * @var PriceInfoExtensionInterfaceFactory|MockObject
     */
    private MockObject $priceInfoExtensionFactory;

    /**
     * @var FormattedPriceInfoBuilder|MockObject
     */
    private MockObject $formattedPriceInfoBuilder;

    /**
     * @var FrameworkPriceInfoInterface|MockObject
     */
    private MockObject $frameworkPriceInfoMock;

    protected function setUp(): void
    {
        $this->priceCurrencyMock = $this->createMock(PriceCurrencyInterface::class);

        // PriceInfoInterface (Catalog API) - used for product render DTO
        $this->renderPriceInfoMock = $this->createMock(PriceInfoInterface::class);

        // PriceInfoExtensionInterface is a generated extension attribute interface
        // All interface methods must be included when using createPartialMockWithReflection
        $this->extensionAttributes = $this->createPartialMockWithReflection(
            PriceInfoExtensionInterface::class,
            [
                // All interface methods from generated interface
                'getMsrp',
                'setMsrp',
                'getTaxAdjustments',
                'setTaxAdjustments',
                'getWeeeAttributes',
                'setWeeeAttributes',
                'getWeeeAdjustment',
                'setWeeeAdjustment'
            ]
        );

        $this->priceInfoFactory = $this->getMockBuilder(PriceInfoInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->priceInfoExtensionFactory = $this->getMockBuilder(PriceInfoExtensionInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->formattedPriceInfoBuilder = $this->createMock(FormattedPriceInfoBuilder::class);

        // Framework PriceInfoInterface - has getPrice() method, returned by Product::getPriceInfo()
        $this->frameworkPriceInfoMock = $this->createMock(FrameworkPriceInfoInterface::class);

        $this->model = new Tax(
            $this->priceCurrencyMock,
            $this->priceInfoExtensionFactory,
            $this->priceInfoFactory,
            $this->formattedPriceInfoBuilder
        );
    }

    /**
     * Test collect method
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCollect(): void
    {
        $amountValue = 10;
        $minAmountValue = 5;
        $storeId = 1;
        $currencyCode = 'usd';

        $productMock = $this->createMock(Product::class);
        $productRender = $this->createMock(ProductRenderInterface::class);
        $finalPrice = $this->createMock(FinalPrice::class);
        $regularPrice = $this->createMock(FinalPrice::class);
        $amount = $this->createMock(AmountInterface::class);
        $minAmount = $this->createMock(AmountInterface::class);
        $maxAmount = $this->createMock(AmountInterface::class);

        // Product::getPriceInfo() returns Framework's PriceInfoInterface (has getPrice())
        $productMock->expects($this->any())
            ->method('getPriceInfo')
            ->willReturn($this->frameworkPriceInfoMock);

        // Framework PriceInfo's getPrice() returns price objects
        $this->frameworkPriceInfoMock->method('getPrice')
            ->willReturnCallback(function ($priceCode) use ($finalPrice, $regularPrice) {
                if ($priceCode === 'final_price') {
                    return $finalPrice;
                }
                if ($priceCode === 'regular_price') {
                    return $regularPrice;
                }
                return $finalPrice;
            });

        // Setup price amounts
        $finalPrice->expects($this->atLeastOnce())
            ->method('getAmount')
            ->willReturn($amount);
        $finalPrice->expects($this->once())
            ->method('getMaximalPrice')
            ->willReturn($maxAmount);
        $finalPrice->expects($this->once())
            ->method('getMinimalPrice')
            ->willReturn($minAmount);

        $regularPrice->expects($this->once())
            ->method('getAmount')
            ->willReturn($amount);

        $amount->expects($this->atLeastOnce())
            ->method('getValue')
            ->with(['tax', 'weee'])
            ->willReturn($amountValue);

        $maxAmount->expects($this->once())
            ->method('getValue')
            ->with(['tax', 'weee'])
            ->willReturn($amountValue);

        $minAmount->expects($this->once())
            ->method('getValue')
            ->with(['tax', 'weee'])
            ->willReturn($minAmountValue);

        // ProductRender::getPriceInfo() returns Catalog API's PriceInfoInterface (DTO)
        $productRender->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($this->renderPriceInfoMock);

        // PriceInfo factory creates new PriceInfo DTOs
        $newPriceInfo = $this->createMock(PriceInfoInterface::class);
        $this->priceInfoFactory->expects($this->once())
            ->method('create')
            ->willReturn($newPriceInfo);

        // Setup extension attributes
        $this->renderPriceInfoMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributes);

        // Extension attributes setTaxAdjustments is called
        $this->extensionAttributes->expects($this->once())
            ->method('setTaxAdjustments')
            ->with($newPriceInfo);

        // Setup price info setters on new DTO
        $newPriceInfo->expects($this->once())->method('setFinalPrice')->with($amountValue);
        $newPriceInfo->expects($this->once())->method('setMaxPrice')->with($amountValue);
        $newPriceInfo->expects($this->once())->method('setMinimalPrice')->with($minAmountValue);
        $newPriceInfo->expects($this->once())->method('setSpecialPrice');
        $newPriceInfo->expects($this->once())->method('setRegularPrice')->with($amountValue);
        $newPriceInfo->method('getFinalPrice')->willReturn($amountValue);

        $this->renderPriceInfoMock->expects($this->once())
            ->method('setExtensionAttributes')
            ->with($this->extensionAttributes);

        $productRender->expects($this->once())
            ->method('setPriceInfo')
            ->with($this->renderPriceInfoMock);

        $productRender->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $productRender->expects($this->once())
            ->method('getCurrencyCode')
            ->willReturn($currencyCode);

        $this->formattedPriceInfoBuilder->expects($this->once())
            ->method('build')
            ->with($newPriceInfo, $storeId, $currencyCode);

        $this->model->collect($productMock, $productRender);
    }
}
