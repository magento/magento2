<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Listing\Collector;

use Magento\Catalog\Api\Data\ProductRenderInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Catalog\Api\Data\ProductRender\PriceInfoInterface;
use Magento\Catalog\Api\Data\ProductRender\PriceInfoInterfaceFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class PriceTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Catalog\Ui\DataProvider\Product\Listing\Collector\Price */
    protected $model;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $priceCurrencyMock;

    /** @var PriceInfoInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $priceInfoFactory;

    /** @var PriceInfoInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $priceMock;

    protected function setUp()
    {
        $this->priceCurrencyMock = $this->getMockBuilder(\Magento\Framework\Pricing\PriceCurrencyInterface::class)
            ->getMockForAbstractClass();
        $this->priceInfoFactory = $this->getMockBuilder(
            \Magento\Catalog\Api\Data\ProductRender\PriceInfoInterfaceFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->priceMock = $this->getMockBuilder(
            \Magento\Catalog\Api\Data\ProductRender\PriceInfoInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            \Magento\Catalog\Ui\DataProvider\Product\Listing\Collector\Price::class,
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
        $productRenderInfoDto = $this->createMock(ProductRenderInterface::class);
        $productRenderInfoDto->expects($this->exactly(2))
            ->method('getPriceInfo')
            ->willReturn([]);
        $priceInfo = $this->getMockBuilder(\Magento\Framework\Pricing\PriceInfo\Base::class)
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
        $price = $this->getMockBuilder(\Magento\Catalog\Pricing\Price\FinalPrice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $priceInfo->expects($this->atLeastOnce())
            ->method('getPrice')
            ->willReturn($price);
        $amount = $this->createMock(AmountInterface::class);

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
