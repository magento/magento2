<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Quote\Model\Cart;

class ShippingMethodConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ShippingMethodConverter
     */
    protected $converter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $builderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $rateModelMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $currencyMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $shippingMethodMock;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->builderMock = $this->getMock(
            '\Magento\Quote\Api\Data\ShippingMethodDataBuilder',
            ['populateWithArray', 'create'],
            [],
            '',
            false
        );
        $this->storeManagerMock = $this->getMock('\Magento\Framework\Store\StoreManagerInterface');
        $this->currencyMock = $this->getMock('\Magento\Directory\Model\Currency', [], [], '', false);
        $this->shippingMethodMock = $this->getMock('\Magento\Quote\Api\Data\ShippingMethodInterfaceFactory',
            [
                'create',
                'setCarrierCode',
                'setMethodCode',
                'setCarrierTitle',
                'setMethodTitle',
                'setAmount',
                'setBaseAmount',
                'setAvailable',
            ],
            [],
            '',
            false);
        $this->rateModelMock = $this->getMock('\Magento\Quote\Model\Quote\Address\Rate',
            [
                'getPrice',
                'getCarrier',
                'getMethod',
                'getCarrierTitle',
                'getMethodTitle',
                '__wakeup',
            ],
            [],
            '',
            false);
        $this->storeMock = $this->getMock('\Magento\Store\Model\Store', [], [], '', false);

        $this->converter = $objectManager->getObject(
            'Magento\Quote\Model\Cart\ShippingMethodConverter',
            [
                'shippingMethodDataFactory' => $this->shippingMethodMock,
                'storeManager' => $this->storeManagerMock,
            ]
        );
    }

    public function testModelToDataObject()
    {
        $this->storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($this->storeMock));
        $this->storeMock->expects($this->once())
            ->method('getBaseCurrency')
            ->will($this->returnValue($this->currencyMock));

        $this->rateModelMock->expects($this->once())->method('getCarrier')->will($this->returnValue('CARRIER_CODE'));
        $this->rateModelMock->expects($this->once())->method('getMethod')->will($this->returnValue('METHOD_CODE'));
        $this->rateModelMock->expects($this->any())->method('getPrice')->will($this->returnValue(90.12));
        $this->currencyMock->expects($this->once())
            ->method('convert')->with(90.12, 'USD')->will($this->returnValue(100.12));
        $this->rateModelMock->expects($this->once())
            ->method('getCarrierTitle')->will($this->returnValue('CARRIER_TITLE'));
        $this->rateModelMock->expects($this->once())
            ->method('getMethodTitle')->will($this->returnValue('METHOD_TITLE'));
        $this->shippingMethodMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->shippingMethodMock));
        $this->shippingMethodMock->expects($this->once())
            ->method('setCarrierCode')
            ->with('CARRIER_CODE')
            ->will($this->returnValue($this->shippingMethodMock));
        $this->shippingMethodMock->expects($this->once())
            ->method('setMethodCode')
            ->with('METHOD_CODE')
            ->will($this->returnValue($this->shippingMethodMock));
        $this->shippingMethodMock->expects($this->once())
            ->method('setCarrierTitle')
            ->with('CARRIER_TITLE')
            ->will($this->returnValue($this->shippingMethodMock));
        $this->shippingMethodMock->expects($this->once())
            ->method('setMethodTitle')
            ->with('METHOD_TITLE')
            ->will($this->returnValue($this->shippingMethodMock));
        $this->shippingMethodMock->expects($this->once())
            ->method('setAmount')
            ->with('100.12')
            ->will($this->returnValue($this->shippingMethodMock));
        $this->shippingMethodMock->expects($this->once())
            ->method('setBaseAmount')
            ->with('90.12')
            ->will($this->returnValue($this->shippingMethodMock));
        $this->shippingMethodMock->expects($this->once())
            ->method('setAvailable')
            ->with(true)
            ->will($this->returnValue($this->shippingMethodMock));

        $this->assertEquals(
            $this->shippingMethodMock,
            $this->converter->modelToDataObject($this->rateModelMock, 'USD')
        );
    }
}
