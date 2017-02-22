<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflinePayments\Test\Unit\Observer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\OfflinePayments\Model\Banktransfer;
use Magento\OfflinePayments\Model\Cashondelivery;

class BeforeOrderPaymentSaveObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\OfflinePayments\Observer\BeforeOrderPaymentSaveObserver
     */
    protected $_model;

    protected function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->_model = $objectManagerHelper
            ->getObject('Magento\OfflinePayments\Observer\BeforeOrderPaymentSaveObserver');
    }

    /**
     * @param string $methodCode
     * @dataProvider dataProviderBeforeOrderPaymentSaveWithInstructions
     */
    public function testBeforeOrderPaymentSaveWithInstructions($methodCode)
    {
        $observer = $this->getMock('Magento\Framework\Event\Observer', ['getEvent'], [], '', false);
        $event = $this->getMock('Magento\Framework\Event', ['getPayment'], [], '', false);
        $payment = $this->getMock(
            'Magento\Sales\Model\Order\Payment',
            ['getMethod', 'setAdditionalInformation', 'getMethodInstance'],
            [],
            '',
            false
        );
        $payment->expects($this->once())
            ->method('getMethod')
            ->willReturn($methodCode);
        $payment->expects($this->once())
            ->method('setAdditionalInformation')
            ->with('instructions', 'payment configuration');
        $method = $this->getMockBuilder('\Magento\OfflinePayments\Model\Banktransfer')
            ->disableOriginalConstructor()
            ->getMock();

        $method->expects($this->once())
            ->method('getInstructions')
            ->willReturn('payment configuration');
        $payment->expects($this->once())
            ->method('getMethodInstance')
            ->willReturn($method);
        $event->expects($this->once())
            ->method('getPayment')
            ->willReturn($payment);
        $observer->expects($this->once())
            ->method('getEvent')
            ->willReturn($event);
        $this->_model->execute($observer);
    }

    public function dataProviderBeforeOrderPaymentSaveWithInstructions()
    {
        return [
            [Banktransfer::PAYMENT_METHOD_BANKTRANSFER_CODE],
            [Cashondelivery::PAYMENT_METHOD_CASHONDELIVERY_CODE],
        ];
    }

    public function testBeforeOrderPaymentSaveWithCheckmo()
    {
        $observer = $this->getMock('Magento\Framework\Event\Observer', ['getEvent'], [], '', false);
        $event = $this->getMock('Magento\Framework\Event', ['getPayment'], [], '', false);
        $payment = $this->getMock(
            'Magento\Sales\Model\Order\Payment',
            ['getMethod', 'setAdditionalInformation', 'getMethodInstance'],
            [],
            '',
            false
        );
        $payment->expects($this->exactly(2))
            ->method('getMethod')
            ->willReturn(\Magento\OfflinePayments\Model\Checkmo::PAYMENT_METHOD_CHECKMO_CODE);
        $payment->expects($this->exactly(2))
            ->method('setAdditionalInformation')
            ->willReturnMap(
                [
                    ['payable_to', 'payable to', $payment],
                    ['mailing_address', 'mailing address', $payment],
                ]
            );

        $method = $this->getMockBuilder('Magento\OfflinePayments\Model\Checkmo')
            ->disableOriginalConstructor()
            ->getMock();
        $method->expects($this->once())
            ->method('getPayableTo')
            ->willReturn('payable to');
        $method->expects($this->once())
            ->method('getMailingAddress')
            ->willReturn('mailing address');
        $payment->expects($this->exactly(2))
            ->method('getMethodInstance')
            ->willReturn($method);
        $event->expects($this->once())
            ->method('getPayment')
            ->willReturn($payment);
        $observer->expects($this->once())
            ->method('getEvent')
            ->willReturn($event);
        $this->_model->execute($observer);
    }

    public function testBeforeOrderPaymentSaveWithOthers()
    {
        $observer = $this->getMock('Magento\Framework\Event\Observer', ['getEvent'], [], '', false);
        $event = $this->getMock('Magento\Framework\Event', ['getPayment'], [], '', false);
        $payment = $this->getMock(
            'Magento\Sales\Model\Order\Payment',
            ['getMethod', 'setAdditionalInformation', 'getMethodInstance'],
            [],
            '',
            false
        );
        $payment->expects($this->exactly(2))
            ->method('getMethod')
            ->willReturn('somepaymentmethod');
        $payment->expects($this->never())
            ->method('setAdditionalInformation');
        $event->expects($this->once())
            ->method('getPayment')
            ->willReturn($payment);
        $observer->expects($this->once())
            ->method('getEvent')
            ->willReturn($event);
        $this->_model->execute($observer);
    }
}
