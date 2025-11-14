<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Ui\DataProvider\Product\Listing\Collector;

use Magento\Bundle\Ui\DataProvider\Product\Listing\Collector\BundlePrice;
use Magento\Catalog\Api\Data\ProductRender\PriceInfoInterface;
use Magento\Catalog\Api\Data\ProductRender\PriceInfoInterfaceFactory;
use Magento\Catalog\Api\Data\ProductRenderInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRender\FormattedPriceInfoBuilder;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Catalog\Test\Unit\Helper\PriceInfoTestHelper;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BundlePriceTest extends TestCase
{
    /**
     * @var BundlePrice
     */
    private $model;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    private $priceCurrencyMock;

    /**
     * @var PriceInfoInterfaceFactory|MockObject
     */
    private $priceInfoFactory;

    /**
     * @var FormattedPriceInfoBuilder|MockObject
     */
    private $formattedPriceInfoBuilder;

    protected function setUp(): void
    {
        $this->priceCurrencyMock = $this->createMock(PriceCurrencyInterface::class);
        $this->priceInfoFactory = $this->createPartialMock(PriceInfoInterfaceFactory::class, ['create']);
        $this->formattedPriceInfoBuilder = $this->createMock(FormattedPriceInfoBuilder::class);

        $this->model = new BundlePrice(
            $this->priceCurrencyMock,
            $this->priceInfoFactory,
            $this->formattedPriceInfoBuilder
        );
    }

    public function testCollect()
    {
        $minAmountValue = 5;
        $amountValue = 10;
        $storeId = 1;
        $currencyCode = 'usd';

        $productMock = $this->createMock(Product::class);
        $price = $this->createMock(FinalPrice::class);
        $productRender = $this->createMock(ProductRenderInterface::class);
        $amount = $this->createMock(AmountInterface::class);
        $minAmount = $this->createMock(AmountInterface::class);
        // Use PriceInfoTestHelper - required for getPrice() method that doesn't exist in parent
        $priceInfo = new PriceInfoTestHelper();

        $productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn('bundle');
        $productRender->expects($this->exactly(2))
            ->method('getPriceInfo')
            ->willReturn($priceInfo);

        // Set price info values directly using TestHelper setters
        $priceInfo->setMaxPrice($amountValue);
        $priceInfo->setMaxRegularPrice($amountValue);
        $priceInfo->setMinimalPrice($minAmountValue);
        $priceInfo->setMinimalRegularPrice($minAmountValue);
        $priceInfo->setPrice($price);

        $productMock->expects($this->exactly(4))
            ->method('getPriceInfo')
            ->willReturn($priceInfo);
        $productMock->method('getPriceInfo')->willReturn($priceInfo);
        $price->expects($this->exactly(2))
            ->method('getMaximalPrice')
            ->willReturn($amount);
        $price->expects($this->exactly(2))
            ->method('getMinimalPrice')
            ->willReturn($minAmount);
        $amount->expects($this->exactly(2))
            ->method('getValue')
            ->willReturn($amountValue);
        $minAmount->expects($this->exactly(2))
            ->method('getValue')
            ->willReturn($minAmountValue);

        $productRender->expects($this->once())
            ->method('getStoreId')
            ->willReturn(1);
        $productRender->expects($this->once())
            ->method('getCurrencyCode')
            ->willReturn($currencyCode);

        $this->formattedPriceInfoBuilder->expects($this->once())
            ->method('build')
            ->with($priceInfo, $storeId, $currencyCode);
        $productRender->expects($this->once())
            ->method('setPriceInfo')
            ->with($priceInfo);

        $this->model->collect($productMock, $productRender);
    }
}
