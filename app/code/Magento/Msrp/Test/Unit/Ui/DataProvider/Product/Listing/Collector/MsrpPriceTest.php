<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Msrp\Test\Unit\Ui\DataProvider\Product\Listing\Collector;

use Magento\Catalog\Api\Data\ProductRender\PriceInfoExtensionFactory;
use Magento\Catalog\Api\Data\ProductRender\PriceInfoExtensionInterface;
use Magento\Catalog\Api\Data\ProductRender\PriceInfoInterface;
use Magento\Catalog\Api\Data\ProductRenderInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Msrp\Api\Data\ProductRender\MsrpPriceInfoInterface;
use Magento\Msrp\Api\Data\ProductRender\MsrpPriceInfoInterfaceFactory;
use Magento\Msrp\Helper\Data;
use Magento\Msrp\Model\Config;
use Magento\Msrp\Ui\DataProvider\Product\Listing\Collector\MsrpPrice;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MsrpPriceTest extends TestCase
{
    /** @var MsrpPrice */
    protected $model;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var PriceCurrencyInterface|MockObject */
    protected $priceCurrencyMock;

    /** @var Data|MockObject */
    protected $msrpHelperMock;

    /** @var Config|MockObject */
    protected $configMock;

    /**
     * @var MsrpPriceInfoInterfaceFactory|MockObject
     */
    private $msrpPriceInfoFactory;

    /**
     * @var MockObject
     */
    private $adjustmentCalculator;

    /**
     * @var PriceInfoExtensionFactory|MockObject
     */
    private $priceInfoExtensionFactory;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->priceCurrencyMock = $this->getMockBuilder(PriceCurrencyInterface::class)
            ->getMockForAbstractClass();
        $this->msrpHelperMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->msrpPriceInfoFactory = $this->getMockBuilder(
            MsrpPriceInfoInterfaceFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->priceInfoExtensionFactory = $this->getMockBuilder(PriceInfoExtensionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->adjustmentCalculator = $this->getMockForAbstractClass(CalculatorInterface::class);
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            MsrpPrice::class,
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
        $productRenderInfoDto = $this->getMockForAbstractClass(ProductRenderInterface::class);
        $productPriceInfo = $this->getMockForAbstractClass(PriceInfoInterface::class);

        $productRenderInfoDto->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($productPriceInfo);
        $extensionAttributes = $this->getMockBuilder(
            PriceInfoExtensionInterface::class
        )
            ->setMethods(['setMsrp'])
            ->getMockForAbstractClass();

        $priceInfo = $this->getMockBuilder(MsrpPriceInfoInterface::class)
            ->setMethods(['getPrice', 'getExtensionAttributes'])
            ->getMockForAbstractClass();
        $amountInterface = $this->getMockForAbstractClass(AmountInterface::class);
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
