<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflineShipping\Test\Unit\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Rate\Result;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FlatrateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\OfflineShipping\Model\Carrier\Flatrate
     */
    private $model;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $errorFactoryMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactoryMock;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $methodFactoryMock;

    /**
     * @var \Magento\OfflineShipping\Model\Carrier\Flatrate\ItemPriceCalculator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceCalculatorMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $helper;

    protected function setUp()
    {

        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['create', 'isSetFlag', 'getValue'])
            ->getMock();

        $this->errorFactoryMock = $this
            ->getMockBuilder(\Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultFactoryMock = $this->getMockBuilder(\Magento\Shipping\Model\Rate\ResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->methodFactoryMock = $this
            ->getMockBuilder(\Magento\Quote\Model\Quote\Address\RateResult\MethodFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->priceCalculatorMock = $this
            ->getMockBuilder(\Magento\OfflineShipping\Model\Carrier\Flatrate\ItemPriceCalculator::class)
            ->disableOriginalConstructor()
            ->setMethods(['getShippingPricePerOrder'])
            ->getMock();

        $this->helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $this->helper->getObject(
            \Magento\OfflineShipping\Model\Carrier\Flatrate::class,
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
     * @dataProvider collectRatesWithGlobalFreeShippingDataProvider
     * @return void
     */
    public function testCollectRatesWithGlobalFreeShipping($freeshipping)
    {
        $expectedPrice = 5;

        $request = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address\RateRequest::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllItems'])
            ->getMock();

        $item = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(
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

        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['isVirtual'])
            ->getMock();

        $this->scopeConfigMock->expects($this->any())->method('isSetFlag')->willReturn(true);
        $this->scopeConfigMock->expects($this->any())->method('getValue')->willReturnMap([
            ['carriers/flatrate/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, null, true],
            ['carriers/flatrate/price', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, null, 5],
            ['carriers/flatrate/type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, null, 'O'],
            ['carriers/flatrate/handling_fee', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, null, 0],
            [
                'carriers/flatrate/handling_type',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                null,
                AbstractCarrier::HANDLING_TYPE_FIXED
            ],
            [
                'carriers/flatrate/handling_action',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
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
            ->setMethods(['setCarrier', 'setCarrierTitle', 'setMethod', 'setMethodTitle', 'setPrice', 'setCost'])
            ->getMock();
        $this->methodFactoryMock->expects($this->once())->method('create')->willReturn($method);

        $result = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->setMethods(['append'])
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
     * @return \PHPUnit_Framework_Constraint_Callback
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
    public function collectRatesWithGlobalFreeShippingDataProvider()
    {
        return [
            ['freeshipping' => true],
            ['freeshipping' => false]
        ];
    }
}
