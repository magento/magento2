<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Service\V1\Cart\PaymentMethod;

use Magento\Checkout\Service\V1\Data\Cart\PaymentMethod;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Checkout\Service\V1\Data\Cart\PaymentMethod\Converter
     */
    protected $converter;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMethodBuilderMock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->paymentMethodBuilderMock = $this->getMock(
            '\Magento\Checkout\Service\V1\Data\Cart\PaymentMethodBuilder',
            ['populateWithArray', 'create'],
            [],
            '',
            false
        );

        $this->converter = $this->objectManager->getObject(
            'Magento\Checkout\Service\V1\Data\Cart\PaymentMethod\Converter',
            [
                'builder' => $this->paymentMethodBuilderMock,
            ]
        );
    }

    public function testConvertQuotePaymentObjectToPaymentDataObject()
    {
        $paymentMock = $this->getMock('\Magento\Sales\Model\Quote\Payment',
            [
                'getMethod', 'getPoNumber', 'getCcOwner', 'getCcNumber',
                'getCcType', 'getCcExpYear', 'getCcExpMonth', 'getAdditionalData', '__wakeup'
            ],
            [],
            '',
            false
        );
        $paymentMock->expects($this->once())->method('getMethod')->will($this->returnValue('checkmo'));
        $paymentMock->expects($this->once())->method('getPoNumber')->will($this->returnValue(100));
        $paymentMock->expects($this->once())->method('getCcOwner')->will($this->returnValue('tester'));
        $paymentMock->expects($this->once())->method('getCcNumber')->will($this->returnValue(100200300));
        $paymentMock->expects($this->once())->method('getCcType')->will($this->returnValue('visa'));
        $paymentMock->expects($this->once())->method('getCcExpYear')->will($this->returnValue(2014));
        $paymentMock->expects($this->once())->method('getCcExpMonth')->will($this->returnValue(10));
        $paymentMock->expects($this->once())->method('getAdditionalData')->will($this->returnValue('test'));

        $data = [
            PaymentMethod::METHOD => 'checkmo',
            PaymentMethod::PO_NUMBER => 100,
            PaymentMethod::CC_OWNER => 'tester',
            PaymentMethod::CC_NUMBER => 100200300,
            PaymentMethod::CC_TYPE => 'visa',
            PaymentMethod::CC_EXP_YEAR => 2014,
            PaymentMethod::CC_EXP_MONTH => 10,
            PaymentMethod::PAYMENT_DETAILS => 'test',
        ];

        $this->paymentMethodBuilderMock->expects($this->once())
            ->method('populateWithArray')
            ->with($data)
            ->will($this->returnSelf());

        $paymentMethodMock = $this->getMock('\Magento\Checkout\Service\V1\Data\PaymentMethod', [], [], '', false);

        $this->paymentMethodBuilderMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($paymentMethodMock));

        $this->assertEquals($paymentMethodMock, $this->converter->toDataObject($paymentMock));
    }
}
