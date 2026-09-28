<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
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
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Pricing\PriceInfo\Base as BasePriceInfo;
use Magento\Msrp\Api\Data\ProductRender\MsrpPriceInfoInterface;
use Magento\Msrp\Api\Data\ProductRender\MsrpPriceInfoInterfaceFactory;
use Magento\Msrp\Helper\Data;
use Magento\Msrp\Model\Config;
use Magento\Msrp\Ui\DataProvider\Product\Listing\Collector\MsrpPrice;
use Magento\Msrp\Pricing\Price\MsrpPrice as MsrpPriceModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MsrpPriceTest extends TestCase
{
    use MockCreationTrait;

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
        $this->priceCurrencyMock = $this->createMock(PriceCurrencyInterface::class);
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
            ->onlyMethods(['create'])
            ->getMock();
        $this->priceInfoExtensionFactory = $this->getMockBuilder(PriceInfoExtensionFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->adjustmentCalculator = $this->createMock(CalculatorInterface::class);
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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCollect(): void
    {
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productRenderInfoDto = $this->createMock(ProductRenderInterface::class);
        $productPriceInfo = $this->createMock(PriceInfoInterface::class);

        $productRenderInfoDto->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($productPriceInfo);
        // PriceInfoExtensionInterface is a generated interface - use createPartialMockWithReflection
        $extensionAttributes = $this->createPartialMockWithReflection(
            PriceInfoExtensionInterface::class,
            [
                'setMsrp',
                'getMsrp',
                'getTaxAdjustments',
                'setTaxAdjustments',
                'getWeeeAttributes',
                'setWeeeAttributes',
                'getWeeeAdjustment',
                'setWeeeAdjustment'
            ]
        );

        // MsrpPriceInfoInterface is a generated interface - use createPartialMockWithReflection
        $msrpPriceInfo = $this->createPartialMockWithReflection(
            MsrpPriceInfoInterface::class,
            [
                'setIsApplicable',
                'getIsApplicable',
                'setExplanationMessage',
                'getExplanationMessage',
                'setMsrpMessage',
                'getMsrpMessage',
                'setIsShownPriceOnGesture',
                'getIsShownPriceOnGesture',
                'setMsrpPrice',
                'getMsrpPrice',
                'getExtensionAttributes',
                'setExtensionAttributes'
            ]
        );
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
            ->willReturn($msrpPriceInfo);
        $this->priceInfoExtensionFactory->expects($this->once())
            ->method('create')
            ->willReturn($extensionAttributes);
        $price = $this->getMockBuilder(MsrpPriceModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        // Product's PriceInfo needs getPrice method
        $priceInfo = $this->createPartialMockWithReflection(
            BasePriceInfo::class,
            ['getPrice']
        );
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
