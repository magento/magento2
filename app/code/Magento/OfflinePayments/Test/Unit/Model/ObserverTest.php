<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflinePayments\Test\Unit\Model;

class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\OfflinePayments\Model\Observer
     */
    protected $_model;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_model = $objectManagerHelper->getObject('Magento\OfflinePayments\Model\Observer');
    }

    public function testBeforeOrderPaymentSave()
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
            ->willReturn('banktransfer');
        $payment->expects($this->once())
            ->method('setAdditionalInformation')
            ->with('instructions', 'payment configuration');
        $method = $this->getMock(
            'Magento\Payment\Model\MethodInterface',
            ['getConfigData', 'getFormBlockType', 'getTitle', 'getCode'],
            [],
            '',
            false
        );
        $method->expects($this->once())
            ->method('getConfigData')
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
        $this->_model->beforeOrderPaymentSave($observer);
    }

    public function testBeforeOrderPaymentSaveNoBanktransfer()
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
            ->willReturn('somepaymentmethod');
        $payment->expects($this->never())
            ->method('setAdditionalInformation');
        $event->expects($this->once())
            ->method('getPayment')
            ->willReturn($payment);
        $observer->expects($this->once())
            ->method('getEvent')
            ->willReturn($event);
        $this->_model->beforeOrderPaymentSave($observer);
    }
}
