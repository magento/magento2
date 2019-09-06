<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Msrp\Test\Unit\Ui\DataProvider\Product\Listing\Collector;

use Magento\Catalog\Api\Data\ProductRenderInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Msrp\Api\Data\ProductRender\MsrpPriceInfoInterfaceFactory;
use Magento\Msrp\Api\Data\ProductRender\MsrpPriceInfoInterface;
use Magento\Catalog\Api\Data\ProductRender\PriceInfoInterface;
use Magento\Catalog\Api\Data\ProductRender\PriceInfoExtensionFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MsrpPriceTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Msrp\Ui\DataProvider\Product\Listing\Collector\MsrpPrice */
    protected $model;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $priceCurrencyMock;

    /** @var \Magento\Msrp\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $msrpHelperMock;

    /** @var \Magento\Msrp\Model\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $configMock;

    /**
     * @var \Magento\Msrp\Api\Data\ProductRender\MsrpPriceInfoInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $msrpPriceInfoFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $adjustmentCalculator;

    /**
     * @var \Magento\Catalog\Api\Data\ProductRender\PriceInfoExtensionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceInfoExtensionFactory;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->priceCurrencyMock = $this->getMockBuilder(\Magento\Framework\Pricing\PriceCurrencyInterface::class)
            ->getMockForAbstractClass();
        $this->msrpHelperMock = $this->getMockBuilder(\Magento\Msrp\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configMock = $this->getMockBuilder(\Magento\Msrp\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->msrpPriceInfoFactory = $this->getMockBuilder(
            \Magento\Msrp\Api\Data\ProductRender\MsrpPriceInfoInterfaceFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->priceInfoExtensionFactory = $this->getMockBuilder(PriceInfoExtensionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->adjustmentCalculator = $this->createMock(CalculatorInterface::class);
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            \Magento\Msrp\Ui\DataProvider\Product\Listing\Collector\MsrpPrice::class,
            [
                'priceCurrency' => $this->priceCurrencyMock,
                'msrpHelper' => $this->msrpHelperMock,
                'config' => $this->configMock,
                'msrpPriceInfoFactory' => $this->msrpPriceInfoFactory,
                'priceInfoExtensionFactory' => $this->priceInfoExtensionFactory,
                'adjustmentCalculator' => $this->adjustmentCalculator
            ]
        );
    }

    /**
     * @return void
     */
    public function testCollect()
    {
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productRenderInfoDto = $this->createMock(ProductRenderInterface::class);
        $productPriceInfo = $this->createMock(PriceInfoInterface::class);

        $productRenderInfoDto->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($productPriceInfo);
        $extensionAttributes = $this->getMockBuilder(
            \Magento\Catalog\Api\Data\ProductRender\PriceInfoExtensionInterface::class
        )
            ->setMethods(['setMsrp'])
            ->getMockForAbstractClass();

        $priceInfo = $this->getMockBuilder(MsrpPriceInfoInterface::class)
            ->setMethods(['getPrice', 'getExtensionAttributes'])
            ->getMockForAbstractClass();
        $amountInterface = $this->createMock(AmountInterface::class);
        $amountInterface->expects($this->once())
            ->method('getValue')
            ->willReturn(20);
        $this->adjustmentCalculator->expects($this->once())
            ->method('getAmount')
            ->willReturn($amountInterface);
        $extensionAttributes->expects($this->once())
            ->method('setMsrp');
        $this->msrpPriceInfoFactory->expects($this->once())
            ->method('create')
            ->willReturn($priceInfo);
        $this->priceInfoExtensionFactory->expects($this->once())
            ->method('create')
            ->willReturn($extensionAttributes);
        $price = $this->getMockBuilder(\Magento\Msrp\Pricing\Price\MsrpPrice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $priceInfo->expects($this->once())
            ->method('getPrice')
            ->with('msrp_price')
            ->willReturn($price);
        $price->expects($this->once())
            ->method('canApplyMsrp')
            ->with($product)
            ->willReturn(true);
        $price->expects($this->once())
            ->method('isMinimalPriceLessMsrp')
            ->with($product)
            ->willReturn(true);
        $this->msrpHelperMock->expects($this->once())
            ->method('isShowPriceOnGesture')
            ->with($product)
            ->willReturn(true);
        $this->msrpHelperMock->expects($this->once())
            ->method('getMsrpPriceMessage')
            ->with($product)
            ->willReturn('Some Message');
        $this->configMock->expects($this->once())
            ->method('getExplanationMessage')
            ->willReturn('Some Explanation Message');
        $this->priceCurrencyMock
            ->expects($this->once())
            ->method('format')
            ->willReturn('<span>$10</span>');
        $product->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($priceInfo);

        $productRenderInfoDto->expects($this->once())
            ->method('setPriceInfo')
            ->with($productPriceInfo);

        $this->model->collect($product, $productRenderInfoDto);
    }
}
