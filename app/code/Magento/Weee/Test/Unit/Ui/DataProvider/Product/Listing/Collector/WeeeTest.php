<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Test\Unit\Ui\DataProvider\Product\Listing\Collector;

use Magento\Catalog\Api\Data\ProductRender\PriceInfoExtensionInterfaceFactory;
use Magento\Catalog\Api\Data\ProductRenderInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRender\FormattedPriceInfoBuilder;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Catalog\Api\Data\ProductRender\PriceInfoExtensionInterface;
use Magento\Weee\Api\Data\ProductRender\WeeeAdjustmentAttributeInterfaceFactory;
use Magento\Weee\Api\Data\ProductRender\WeeeAdjustmentAttributeInterface;
use Magento\Weee\Ui\DataProvider\Product\Listing\Collector\Weee;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class WeeeTest extends \PHPUnit\Framework\TestCase
{
    /** @var Weee */
    protected $model;

    /** @var \Magento\Weee\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $weeeHelperMock;

    /** @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $priceCurrencyMock;

    /** @var PriceInfoExtensionInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $extensionAttributes;

    /** @var WeeeAdjustmentAttributeInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $weeeAdjustmentAttributeFactory;

    /** @var PriceInfoExtensionInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $priceInfoExtensionFactory;

    /** @var FormattedPriceInfoBuilder|\PHPUnit_Framework_MockObject_MockObject */
    private $formattedPriceInfoBuilder;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->weeeHelperMock = $this->getMockBuilder(\Magento\Weee\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->priceCurrencyMock = $this->getMockBuilder(\Magento\Framework\Pricing\PriceCurrencyInterface::class)
            ->getMockForAbstractClass();

        $this->weeeAdjustmentAttributeFactory = $this->getMockBuilder(WeeeAdjustmentAttributeInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->extensionAttributes = $this->getMockBuilder(PriceInfoExtensionInterface::class)
            ->setMethods(['setWeeeAttributes', 'setWeeeAdjustment'])
            ->getMockForAbstractClass();

        $this->priceInfoExtensionFactory = $this->getMockBuilder(PriceInfoExtensionInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->formattedPriceInfoBuilder = $this->getMockBuilder(FormattedPriceInfoBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

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
        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productRender = $this->getMockBuilder(ProductRenderInterface::class)
            ->setMethods(['getPriceInfo', 'getStoreId'])
            ->getMockForAbstractClass();
        $weeAttribute  = $this->getMockBuilder(WeeeAdjustmentAttributeInterface::class)
            ->setMethods(['getData'])
            ->getMockForAbstractClass();
        $this->weeeAdjustmentAttributeFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($weeAttribute);
        $priceInfo = $this->getMockBuilder(\Magento\Framework\Pricing\PriceInfo\Base::class)
            ->disableOriginalConstructor()
            ->setMethods(['getExtensionAttributes', 'getPrice', 'setExtensionAttributes'])
            ->getMock();
        $price = $this->getMockBuilder(\Magento\Catalog\Pricing\Price\FinalPrice::class)
            ->disableOriginalConstructor()
            ->getMock();
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
            ->withConsecutive(
                [],
                ['amount'],
                ['tax_amount'],
                ['amount_excl_tax']
            )
            ->willReturnOnConsecutiveCalls(
                [
                    'amount' => 12.1,
                    'tax_amount' => 12,
                    'amount_excl_tax' => 71
                ],
                12.1,
                12.1,
                12.1,
                12.1
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
