<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflineShipping\Test\Unit\Model\Carrier;

use Magento\OfflineShipping\Model\ResourceModel\Carrier\TablerateFactory;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Shipping\Model\Rate\Result;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TablerateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\OfflineShipping\Model\Carrier\Tablerate
     */
    private $model;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $scopeConfigMock;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $errorFactoryMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $loggerMock;

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resultFactoryMock;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $methodFactoryMock;

    /**
     * @var TablerateFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $tablerateFactoryMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $helper;

    protected function setUp(): void
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

        $this->tablerateFactoryMock = $this
            ->getMockBuilder(\Magento\OfflineShipping\Model\ResourceModel\Carrier\TablerateFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create', 'getRate'])
            ->getMock();

        $this->helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $this->helper->getObject(
            \Magento\OfflineShipping\Model\Carrier\Tablerate::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                'rateErrorFactory' => $this->errorFactoryMock,
                'logger' => $this->loggerMock,
                'rateResultFactory' => $this->resultFactoryMock,
                'resultMethodFactory' => $this->methodFactoryMock,
                'tablerateFactory' => $this->tablerateFactoryMock
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
        $rate = [
            'price' => 15,
            'cost' => 2
        ];

        $request = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address\RateRequest::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllItems', 'getPackageQty', 'getFreeShipping'])
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
                    'getFreeShipping',
                    'getBaseRowTotal'
                ]
            )
            ->getMock();

        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['isVirtual'])
            ->getMock();

        $tablerate = $this->getMockBuilder(\Magento\OfflineShipping\Model\Carrier\Tablerate::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRate'])
            ->getMock();

        $this->scopeConfigMock->expects($this->any())->method('isSetFlag')->willReturn(true);

        $tablerate->expects($this->any())->method('getRate')->willReturn($rate);
        $this->tablerateFactoryMock->expects($this->once())->method('create')->willReturn($tablerate);

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

        $returnPrice = null;
        $method->expects($this->once())->method('setPrice')->with($this->captureArg($returnPrice));

        $returnCost = null;
        $method->expects($this->once())->method('setCost')->with($this->captureArg($returnCost));

        $returnMethod = null;
        $result->expects($this->once())->method('append')->with($this->captureArg($returnMethod));

        $request->expects($this->never())->method('getFreeShipping')->willReturn($freeshipping);

        $returnResult = $this->model->collectRates($request);

        $this->assertEquals($rate['price'], $returnPrice);
        $this->assertEquals($rate['cost'], $returnCost);
        $this->assertEquals($method, $returnMethod);
        $this->assertEquals($result, $returnResult);
    }

    /**
     * Captures the argument and saves it in the given variable
     *
     * @param $captureVar
     * @return \PHPUnit\Framework\Constraint\Callback
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
