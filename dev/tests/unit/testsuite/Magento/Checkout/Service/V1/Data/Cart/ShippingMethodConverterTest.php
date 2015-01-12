<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Service\V1\Data\Cart;

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
            '\Magento\Checkout\Service\V1\Data\Cart\ShippingMethodBuilder',
            ['populateWithArray', 'create'],
            [],
            '',
            false
        );
        $this->storeManagerMock = $this->getMock('\Magento\Store\Model\StoreManagerInterface');
        $this->currencyMock = $this->getMock('\Magento\Directory\Model\Currency', [], [], '', false);
        $this->shippingMethodMock =
            $this->getMock('\Magento\Checkout\Service\V1\Data\Cart\ShippingMethod', [], [], '', false);
        $this->rateModelMock = $this->getMock('\Magento\Sales\Model\Quote\Address\Rate',
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
            'Magento\Checkout\Service\V1\Data\Cart\ShippingMethodConverter',
            [
                'builder' => $this->builderMock,
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
        $data = [
            ShippingMethod::CARRIER_CODE => 'CARRIER_CODE',
            ShippingMethod::METHOD_CODE => 'METHOD_CODE',
            ShippingMethod::CARRIER_TITLE => 'CARRIER_TITLE',
            ShippingMethod::METHOD_TITLE => 'METHOD_TITLE',
            ShippingMethod::SHIPPING_AMOUNT => '100.12',
            ShippingMethod::BASE_SHIPPING_AMOUNT => '90.12',
            ShippingMethod::AVAILABLE => true,
        ];

        $this->rateModelMock->expects($this->once())->method('getCarrier')->will($this->returnValue('CARRIER_CODE'));
        $this->rateModelMock->expects($this->once())->method('getMethod')->will($this->returnValue('METHOD_CODE'));
        $this->rateModelMock->expects($this->any())->method('getPrice')->will($this->returnValue(90.12));
        $this->currencyMock->expects($this->once())
            ->method('convert')->with(90.12, 'USD')->will($this->returnValue(100.12));
        $this->rateModelMock->expects($this->once())
            ->method('getCarrierTitle')->will($this->returnValue('CARRIER_TITLE'));
        $this->rateModelMock->expects($this->once())
            ->method('getMethodTitle')->will($this->returnValue('METHOD_TITLE'));
        $this->builderMock->expects($this->once())
            ->method('populateWithArray')->with($data)->will($this->returnValue($this->builderMock));
        $this->builderMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->shippingMethodMock));

        $this->assertEquals(
            $this->shippingMethodMock,
            $this->converter->modelToDataObject($this->rateModelMock, 'USD')
        );
    }
}
