<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Quote\Test\Unit\Model\Quote\Payment;

use Magento\Payment\Model\Method\Substitution;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class ToOrderPaymentTest tests converter to order payment
 */
class ToOrderPaymentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Api\OrderPaymentRepositoryInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderPaymentRepositoryMock;

    /**
     * @var \Magento\Framework\DataObject\Copy | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectCopyMock;

    /**
     * @var \Magento\Quote\Model\Quote\Payment | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMock;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var \Magento\Quote\Model\Quote\Payment\ToOrderPayment
     */
    protected $converter;

    protected function setUp()
    {
        $this->paymentMock = $this->getMock(
            'Magento\Quote\Model\Quote\Payment',
            ['getCcNumber', 'getCcCid', 'getMethodInstance', 'getAdditionalInformation'],
            [],
            '',
            false
        );
        $this->objectCopyMock = $this->getMock('Magento\Framework\DataObject\Copy', [], [], '', false);
        $this->orderPaymentRepositoryMock = $this->getMockForAbstractClass(
            'Magento\Sales\Api\OrderPaymentRepositoryInterface',
            [],
            '',
            false,
            false
        );
        $this->dataObjectHelper = $this->getMock('\Magento\Framework\Api\DataObjectHelper', [], [], '', false);
        $objectManager = new ObjectManager($this);
        $this->converter = $objectManager->getObject(
            'Magento\Quote\Model\Quote\Payment\ToOrderPayment',
            [
                'orderPaymentRepository' => $this->orderPaymentRepositoryMock,
                'objectCopyService' => $this->objectCopyMock,
                'dataObjectHelper' => $this->dataObjectHelper
            ]
        );
    }

    /**
     * Tests Convert method in payment to order payment converter
     */
    public function testConvert()
    {
        $methodInterface = $this->getMockForAbstractClass('Magento\Payment\Model\MethodInterface');

        $paymentData = ['test' => 'test2'];
        $data = ['some_id' => 1];
        $paymentMethodTitle = 'TestTitle';
        $additionalInfo = ['token' => 'TOKEN-123'];

        $this->paymentMock->expects($this->once())->method('getMethodInstance')->willReturn($methodInterface);
        $methodInterface->expects($this->once())->method('getTitle')->willReturn($paymentMethodTitle);
        $this->objectCopyMock->expects($this->once())->method('getDataFromFieldset')->with(
            'quote_convert_payment',
            'to_order_payment',
            $this->paymentMock
        )->willReturn($paymentData);

        $this->paymentMock->expects($this->once())
            ->method('getAdditionalInformation')
            ->willReturn($additionalInfo);
        $ccNumber = 123456798;
        $ccCid = 1234;
        $this->paymentMock->expects($this->once())
            ->method('getCcNumber')
            ->willReturn($ccNumber);
        $this->paymentMock->expects($this->once())
            ->method('getCcCid')
            ->willReturn($ccCid);

        $orderPayment = $this->getMockForAbstractClass(
            'Magento\Sales\Api\Data\OrderPaymentInterface',
            [],
            '',
            false,
            true,
            true,
            ['setCcNumber', 'setCcCid', 'setAdditionalInformation']
        );
        $orderPayment->expects($this->once())
            ->method('setAdditionalInformation')
            ->with(array_merge($additionalInfo, [Substitution::INFO_KEY_TITLE => $paymentMethodTitle]))
            ->willReturnSelf();
        $orderPayment->expects($this->once())
            ->method('setCcNumber')
            ->willReturnSelf();
        $orderPayment->expects($this->once())
            ->method('setCcCid')
            ->willReturnSelf();

        $this->orderPaymentRepositoryMock->expects($this->once())->method('create')->willReturn($orderPayment);
        $this->dataObjectHelper->expects($this->once())
            ->method('populateWithArray')
            ->with($orderPayment, array_merge($paymentData, $data), '\Magento\Sales\Api\Data\OrderPaymentInterface')
            ->willReturnSelf();

        $this->assertSame($orderPayment, $this->converter->convert($this->paymentMock, $data));
    }
}
