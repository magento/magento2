<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Listing\Collector;

use Magento\Catalog\Api\Data\ProductRender\PriceInfoInterface;
use Magento\Catalog\Api\Data\ProductRender\PriceInfoInterfaceFactory;
use Magento\Catalog\Api\Data\ProductRenderInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Catalog\Ui\DataProvider\Product\Listing\Collector\Price;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\PriceInfo\Base;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PriceTest extends TestCase
{
    /** @var Price */
    protected $model;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var PriceCurrencyInterface|MockObject */
    protected $priceCurrencyMock;

    /** @var PriceInfoInterfaceFactory|MockObject */
    private $priceInfoFactory;

    /** @var PriceInfoInterface|MockObject */
    private $priceMock;

    protected function setUp(): void
    {
        $this->priceCurrencyMock = $this->getMockBuilder(PriceCurrencyInterface::class)
            ->getMockForAbstractClass();
        $this->priceInfoFactory = $this->getMockBuilder(
            PriceInfoInterfaceFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->priceMock = $this->getMockBuilder(
            PriceInfoInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            Price::class,
            [
                'priceCurrency' => $this->priceCurrencyMock,
                'priceInfoFactory' => $this->priceInfoFactory,
            ]
        );
    }

    public function testGet()
    {
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productRenderInfoDto = $this->getMockForAbstractClass(ProductRenderInterface::class);
        $productRenderInfoDto->expects($this->exactly(2))
            ->method('getPriceInfo')
            ->willReturn([]);
        $priceInfo = $this->getMockBuilder(Base::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->priceInfoFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->priceMock);
        $this->priceMock->expects($this->once())
            ->method('setFinalPrice')
            ->with(10);
        $this->priceMock->expects($this->once())
            ->method('setMinimalPrice')
            ->with(10);
        $this->priceMock->expects($this->once())
            ->method('setMaxPrice')
            ->with(10);
        $this->priceMock->expects($this->once())
            ->method('setRegularPrice')
            ->with(10);
        $price = $this->getMockBuilder(FinalPrice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $priceInfo->expects($this->atLeastOnce())
            ->method('getPrice')
            ->willReturn($price);
        $amount = $this->getMockForAbstractClass(AmountInterface::class);

        $price->expects($this->atLeastOnce())
            ->method('getAmount')
            ->willReturn($amount);
        $price->expects($this->atLeastOnce())
            ->method('getMinimalPrice')
            ->willReturn($amount);
        $price->expects($this->atLeastOnce())
            ->method('getMaximalPrice')
            ->willReturn($amount);
        $amount->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturnOnConsecutiveCalls(10, 10, 10, 10);
        $product->expects($this->atLeastOnce())
            ->method('getPriceInfo')
            ->willReturn($priceInfo);
        $productRenderInfoDto->expects($this->once())
            ->method('setPriceInfo')
            ->with($this->priceMock);

        $this->model->collect($product, $productRenderInfoDto);
    }
}
