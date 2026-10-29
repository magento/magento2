<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Weee\Test\Unit\Ui\DataProvider\Product\Listing\Collector;

use Magento\Catalog\Api\Data\ProductRender\PriceInfoExtensionInterface;
use Magento\Catalog\Api\Data\ProductRender\PriceInfoExtensionInterfaceFactory;
use Magento\Catalog\Api\Data\ProductRenderInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRender\FormattedPriceInfoBuilder;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\PriceInfo\Base;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Weee\Api\Data\ProductRender\WeeeAdjustmentAttributeInterface;
use Magento\Weee\Api\Data\ProductRender\WeeeAdjustmentAttributeInterfaceFactory;
use Magento\Weee\Helper\Data;
use Magento\Weee\Model\ProductRender\WeeeAdjustmentAttribute;
use Magento\Weee\Ui\DataProvider\Product\Listing\Collector\Weee;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class WeeeTest extends TestCase
{
    use MockCreationTrait;

    /** @var Weee */
    protected $model;

    /** @var Data|MockObject */
    protected $weeeHelperMock;

    /** @var PriceCurrencyInterface|MockObject */
    protected $priceCurrencyMock;

    /** @var PriceInfoExtensionInterface|MockObject */
    private $extensionAttributes;

    /** @var WeeeAdjustmentAttributeInterfaceFactory|MockObject */
    private $weeeAdjustmentAttributeFactory;

    /** @var PriceInfoExtensionInterfaceFactory|MockObject */
    private $priceInfoExtensionFactory;

    /** @var FormattedPriceInfoBuilder|MockObject */
    private $formattedPriceInfoBuilder;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->weeeHelperMock = $this->createMock(Data::class);
        $this->priceCurrencyMock = $this->createMock(PriceCurrencyInterface::class);

        $this->weeeAdjustmentAttributeFactory = $this->createPartialMock(
            WeeeAdjustmentAttributeInterfaceFactory::class,
            ['create']
        );

        $this->extensionAttributes = $this->createPartialMockWithReflection(
            PriceInfoExtensionInterface::class,
            ['setWeeeAttributes', 'setWeeeAdjustment']
        );

        $this->priceInfoExtensionFactory = $this->createPartialMock(
            PriceInfoExtensionInterfaceFactory::class,
            ['create']
        );

        $this->formattedPriceInfoBuilder = $this->createMock(FormattedPriceInfoBuilder::class);

        $this->model = new Weee(
            $this->weeeHelperMock,
            $this->priceCurrencyMock,
            $this->weeeAdjustmentAttributeFactory,
            $this->priceInfoExtensionFactory,
            $this->formattedPriceInfoBuilder
        );
    }

    /**
     * @return void
     */
    public function testCollect()
    {
        $productMock = $this->createMock(Product::class);
        $productRender = $this->createMock(ProductRenderInterface::class);
        $weeAttribute  = $this->createPartialMockWithReflection(
            WeeeAdjustmentAttribute::class,
            ['getData', 'setAttributeCode']
        );
        $this->weeeAdjustmentAttributeFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($weeAttribute);
        $priceInfo = $this->createPartialMockWithReflection(
            Base::class,
            ['getPrice', 'getExtensionAttributes', 'setExtensionAttributes']
        );
        $price = $this->createMock(FinalPrice::class);
        $weeAttribute->expects($this->once())
            ->method('setAttributeCode')
            ->with();
        $productRender->expects($this->any())
            ->method('getPriceInfo')
            ->willReturn($priceInfo);
        $priceInfo->expects($this->any())
            ->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributes);
        $productMock->expects($this->any())
            ->method('getPriceInfo')
            ->willReturn($priceInfo);
        $priceInfo->expects($this->atLeastOnce())
            ->method('getPrice')
            ->willReturn($price);
        $amount = $this->createMock(AmountInterface::class);
        $productRender->expects($this->exactly(5))
            ->method('getStoreId')
            ->willReturn(1);
        $productRender->expects($this->exactly(5))
            ->method('getCurrencyCode')
            ->willReturn('USD');
        $price->expects($this->once())
            ->method('getAmount')
            ->willReturn($amount);
        $amount->expects($this->once())
            ->method('getValue')
            ->willReturn(12.1);
        $weeAttributes = ['weee_1' => $weeAttribute];
        $weeAttribute->expects($this->exactly(6))
            ->method('getData')
            ->willReturnCallback(
                function ($arg) {
                     static $callCount = 0;
                    if ($callCount==0) {
                        $callCount++;
                        return [
                            'amount' => 12.1,
                            'tax_amount' => 12,
                            'amount_excl_tax' => 71
                        ];
                    } elseif ($callCount==1 && $arg == 'amount') {
                        $callCount++;
                        return 12.1;
                    } elseif ($callCount==2 && $arg == 'tax_amount') {
                        $callCount++;
                        return 12.1;
                    } elseif ($callCount==3 && $arg == 'amount_excl_tax') {
                        $callCount++;
                        return 12.1;
                    } elseif ($callCount==4) {
                        $callCount++;
                        return 12.1;
                    }
                }
            );
        $this->priceCurrencyMock->expects($this->exactly(5))
            ->method('format')
            ->with(12.1, true, 2, 1, 'USD')
            ->willReturnOnConsecutiveCalls(
                '<span>$12</span>',
                '<span>$12</span>',
                '<span>$71</span>',
                '<span>$83</span>',
                '<span>$12</span>'
            );
        $this->weeeHelperMock->expects($this->once())
            ->method('getProductWeeeAttributesForDisplay')
            ->with($productMock)
            ->willReturn($weeAttributes);
        $priceInfo->expects($this->once())
            ->method('setExtensionAttributes')
            ->with($this->extensionAttributes);

        $this->model->collect($productMock, $productRender);
    }
}
