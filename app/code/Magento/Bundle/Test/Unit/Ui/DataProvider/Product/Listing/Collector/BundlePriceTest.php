<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
        $this->priceCurrencyMock = $this->getMockBuilder(PriceCurrencyInterface::class)
            ->getMockForAbstractClass();
        $this->priceInfoFactory = $this->getMockBuilder(PriceInfoInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->formattedPriceInfoBuilder = $this->getMockBuilder(FormattedPriceInfoBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

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

        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $price = $this->getMockBuilder(FinalPrice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productRender = $this->getMockBuilder(ProductRenderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $amount = $this->getMockBuilder(AmountInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $minAmount = $this->getMockBuilder(AmountInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $priceInfo = $this->getMockBuilder(PriceInfoInterface::class)
            ->setMethods(
                [
                    'getPrice',
                    'setMaxPrice',
                    'setMaxRegularPrice',
                    'setMinimalPrice',
                    'setMinimalRegularPrice'
                ]
            )
            ->getMockForAbstractClass();

        $productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn('bundle');
        $productRender->expects($this->exactly(2))
            ->method('getPriceInfo')
            ->willReturn($priceInfo);
        $priceInfo->expects($this->once())
            ->method('setMaxPrice')
            ->with($amountValue);
        $priceInfo->expects($this->once())
            ->method('setMaxRegularPrice')
            ->with($amountValue);
        $priceInfo->expects($this->once())
            ->method('setMinimalPrice')
            ->with($minAmountValue);
        $priceInfo->expects($this->once())
            ->method('setMinimalRegularPrice')
            ->with($minAmountValue);
        $productMock->expects($this->exactly(4))
            ->method('getPriceInfo')
            ->willReturn($priceInfo);
        $productMock->expects($this->any())
            ->method('getPriceInfo')
            ->willReturn($priceInfo);
        $priceInfo->expects($this->exactly(4))
            ->method('getPrice')
            ->willReturn($price);
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
