<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflinePayments\Test\Unit\Observer;

use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\OfflinePayments\Model\Banktransfer;
use Magento\OfflinePayments\Model\Cashondelivery;
use Magento\OfflinePayments\Observer\BeforeOrderPaymentSaveObserver;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\OfflinePayments\Model\Checkmo;

class BeforeOrderPaymentSaveObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var BeforeOrderPaymentSaveObserver
     */
    protected $_model;

    /**
     * @var Payment|MockObject
     */
    private $payment;

    /**
     * @var Event|MockObject
     */
    private $event;

    /**
     * @var Observer|MockObject
     */
    private $observer;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->event = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPayment'])
            ->getMock();

        $this->event->expects(self::once())
            ->method('getPayment')
            ->willReturn($this->payment);

        $this->observer = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->observer->expects(self::once())
            ->method('getEvent')
            ->willReturn($this->event);

        $this->_model = $objectManagerHelper->getObject(BeforeOrderPaymentSaveObserver::class);
    }

    /**
     * @param string $methodCode
     * @dataProvider dataProviderBeforeOrderPaymentSaveWithInstructions
     */
    public function testBeforeOrderPaymentSaveWithInstructions($methodCode)
    {
        $this->payment->expects(self::once())
            ->method('getMethod')
            ->willReturn($methodCode);
        $this->payment->method('getAdditionalInformation')
            ->with('instructions')
            ->willReturn('payment configuration');
        $this->payment->expects(self::once())
            ->method('setAdditionalInformation')
            ->with('instructions', 'payment configuration');

        $this->_model->execute($this->observer);
    }

    /**
     * Returns list of payment method codes.
     *
     * @return array
     */
    public function dataProviderBeforeOrderPaymentSaveWithInstructions()
    {
        return [
            [Banktransfer::PAYMENT_METHOD_BANKTRANSFER_CODE],
            [Cashondelivery::PAYMENT_METHOD_CASHONDELIVERY_CODE],
        ];
    }

    public function testBeforeOrderPaymentSaveWithCheckmo()
    {
        $this->payment->expects(self::exactly(2))
            ->method('getMethod')
            ->willReturn(Checkmo::PAYMENT_METHOD_CHECKMO_CODE);
        $this->payment->expects(self::exactly(2))
            ->method('setAdditionalInformation')
            ->willReturnMap(
                [
                    ['payable_to', 'payable to', $this->payment],
                    ['mailing_address', 'mailing address', $this->payment],
                ]
            );

        $method = $this->getMockBuilder(Checkmo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $method->expects(self::exactly(2))
            ->method('getPayableTo')
            ->willReturn('payable to');
        $method->expects(self::exactly(2))
            ->method('getMailingAddress')
            ->willReturn('mailing address');
        $this->payment->expects(self::once())
            ->method('getMethodInstance')
            ->willReturn($method);
        $this->_model->execute($this->observer);
    }

    /**
     * Checks a case when payment method is Check Money order and
     * payable person and mailing address do not specified.
     */
    public function testBeforeOrderPaymentSaveWithCheckmoWithoutConfig()
    {
        $this->payment->expects(self::exactly(2))
            ->method('getMethod')
            ->willReturn(Checkmo::PAYMENT_METHOD_CHECKMO_CODE);
        $this->payment->expects(self::never())
            ->method('setAdditionalInformation');

        $method = $this->getMockBuilder(Checkmo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $method->expects(self::once())
            ->method('getPayableTo')
            ->willReturn(null);
        $method->expects(self::once())
            ->method('getMailingAddress')
            ->willReturn(null);
        $this->payment->expects(self::once())
            ->method('getMethodInstance')
            ->willReturn($method);
        $this->_model->execute($this->observer);
    }

    public function testBeforeOrderPaymentSaveWithOthers()
    {
        $this->payment->expects(self::exactly(2))
            ->method('getMethod')
            ->willReturn('somepaymentmethod');
        $this->payment->expects(self::never())
            ->method('setAdditionalInformation');

        $this->_model->execute($this->observer);
    }
}
