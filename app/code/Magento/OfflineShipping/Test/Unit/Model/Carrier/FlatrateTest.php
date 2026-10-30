<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\OfflineShipping\Test\Unit\Model\Carrier;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\OfflineShipping\Model\Carrier\Flatrate;
use Magento\OfflineShipping\Model\Carrier\Flatrate\ItemPriceCalculator;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Sales\Model\Order\Item;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Constraint\Callback;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FlatrateTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Flatrate
     */
    private $model;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var ErrorFactory|MockObject
     */
    private $errorFactoryMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var ResultFactory|MockObject
     */
    private $resultFactoryMock;

    /**
     * @var MethodFactory|MockObject
     */
    private $methodFactoryMock;

    /**
     * @var ItemPriceCalculator|MockObject
     */
    private $priceCalculatorMock;

    /**
     * @var ObjectManager
     */
    private $helper;

    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->createPartialMockWithReflection(
            ScopeConfigInterface::class,
            ['create', 'isSetFlag', 'getValue']
        );

        $this->errorFactoryMock = $this
            ->getMockBuilder(ErrorFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->resultFactoryMock = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->methodFactoryMock = $this
            ->getMockBuilder(MethodFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->priceCalculatorMock = $this
            ->getMockBuilder(ItemPriceCalculator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getShippingPricePerOrder'])
            ->getMock();

        $this->helper = new ObjectManager($this);
        $this->model = $this->helper->getObject(
            Flatrate::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                'rateErrorFactory' => $this->errorFactoryMock,
                'logger' => $this->loggerMock,
                'rateResultFactory' => $this->resultFactoryMock,
                'rateMethodFactory' => $this->methodFactoryMock,
                'itemPriceCalculator' => $this->priceCalculatorMock
            ]
        );
    }

    /**
     * @param bool $freeshipping
     * @return void
     */
    #[DataProvider('collectRatesWithGlobalFreeShippingDataProvider')]
    public function testCollectRatesWithGlobalFreeShipping($freeshipping)
    {
        $this->markTestSkipped('Test needs refactoring.');
        $expectedPrice = 5;

        $request = $this->getMockBuilder(RateRequest::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAllItems', 'getPackageQty', 'getFreeShipping'])
            ->getMock();

        $item = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'getProduct',
                    'getParentItem',
                    'getHasChildren',
                    'isShipSeparately',
                    'getChildren',
                    'getQty',
                    'getFreeShipping'
                ]
            )
            ->getMock();

        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isVirtual'])
            ->getMock();

        $this->scopeConfigMock->expects($this->any())->method('isSetFlag')->willReturn(true);
        $this->scopeConfigMock->expects($this->any())->method('getValue')->willReturnMap([
            ['carriers/flatrate/active', ScopeInterface::SCOPE_STORE, null, true],
            ['carriers/flatrate/price', ScopeInterface::SCOPE_STORE, null, 5],
            ['carriers/flatrate/type', ScopeInterface::SCOPE_STORE, null, 'O'],
            ['carriers/flatrate/handling_fee', ScopeInterface::SCOPE_STORE, null, 0],
            [
                'carriers/flatrate/handling_type',
                ScopeInterface::SCOPE_STORE,
                null,
                AbstractCarrier::HANDLING_TYPE_FIXED
            ],
            [
                'carriers/flatrate/handling_action',
                ScopeInterface::SCOPE_STORE,
                null,
                AbstractCarrier::HANDLING_ACTION_PERORDER
            ],
        ]);

        $this->priceCalculatorMock
            ->expects($this->once())
            ->method('getShippingPricePerOrder')
            ->willReturn($expectedPrice);

        $method = $this->getMockBuilder(Method::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setCarrier', 'setCarrierTitle', 'setMethod', 'setMethodTitle', 'setPrice', 'setCost'])
            ->getMock();
        $this->methodFactoryMock->expects($this->once())->method('create')->willReturn($method);

        $result = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['append'])
            ->getMock();
        $this->resultFactoryMock->expects($this->once())->method('create')->willReturn($result);

        $product->expects($this->any())->method('isVirtual')->willReturn(false);

        $item->expects($this->any())->method('getProduct')->willReturn($product);
        $item->expects($this->any())->method('getFreeShipping')->willReturn(1);
        $item->expects($this->any())->method('getQty')->willReturn(1);

        $request->expects($this->any())->method('getAllItems')->willReturn([$item]);
        $request->expects($this->any())->method('getPackageQty')->willReturn(1);

        $request->expects($this->never())->method('getFreeShipping')->willReturn($freeshipping);

        $returnPrice = null;
        $method->expects($this->once())->method('setPrice')->with($this->captureArg($returnPrice));

        $returnCost = null;
        $method->expects($this->once())->method('setCost')->with($this->captureArg($returnCost));

        $returnMethod = null;
        $result->expects($this->once())->method('append')->with($this->captureArg($returnMethod));

        $returnResult = $this->model->collectRates($request);

        $this->assertEquals($expectedPrice, $returnPrice);
        $this->assertEquals($expectedPrice, $returnCost);
        $this->assertEquals($method, $returnMethod);
        $this->assertEquals($result, $returnResult);
    }

    /**
     * Captures the argument and saves it in the given variable
     *
     * @param $captureVar
     * @return Callback
     */
    private function captureArg(&$captureVar)
    {
        return $this->callback(function ($argToMock) use (&$captureVar) {
            $captureVar = $argToMock;

            return true;
        });
    }

    /**
     * @return array
     */
    public static function collectRatesWithGlobalFreeShippingDataProvider()
    {
        return [
            ['freeshipping' => true],
            ['freeshipping' => false]
        ];
    }

    #[DataProvider('addressFreeShippingDataProvider')]
    public function testCollectRatesWithAddressFreeShipping(bool $freeShipping, float $expectedPrice): void
    {
        $config = [
            'carriers/flatrate/active' => true,
            'carriers/flatrate/price' => 5,
            'carriers/flatrate/type' => 'O',
            'carriers/flatrate/handling_fee' => 0,
            'carriers/flatrate/handling_type' => AbstractCarrier::HANDLING_TYPE_FIXED,
            'carriers/flatrate/handling_action' => AbstractCarrier::HANDLING_ACTION_PERORDER,
        ];
        $this->scopeConfigMock->method('isSetFlag')->willReturnCallback(
            static fn (string $path) => (bool)($config[$path] ?? false)
        );
        $this->scopeConfigMock->method('getValue')->willReturnCallback(
            static fn (string $path) => $config[$path] ?? null
        );
        $this->priceCalculatorMock->method('getShippingPricePerOrder')->willReturn(5.0);

        $priceCurrency = $this->createStub(PriceCurrencyInterface::class);
        $priceCurrency->method('round')->willReturnCallback(static fn ($price) => (float)$price);
        $method = new Method($priceCurrency);
        $this->methodFactoryMock->method('create')->willReturn($method);
        $result = $this->createMock(Result::class);
        $this->resultFactoryMock->method('create')->willReturn($result);
        $result->expects($this->once())->method('append')->with($method);

        $product = $this->createStub(Product::class);
        $product->method('isVirtual')->willReturn(false);
        $item = $this->createStub(QuoteItem::class);
        $item->method('getProduct')->willReturn($product);
        $item->method('getQty')->willReturn(1);

        $request = new RateRequest();
        $request->setAllItems([$item]);
        $request->setPackageQty(2);
        $request->setFreeShipping($freeShipping);

        $this->model->collectRates($request);

        $this->assertSame($expectedPrice, $method->getPrice());
    }

    /**
     * @return array
     */
    public static function addressFreeShippingDataProvider(): array
    {
        return [
            'address free shipping' => [true, 0.0],
            'no free shipping' => [false, 5.0],
        ];
    }
}
