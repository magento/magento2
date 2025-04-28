<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\OfflinePayments\Test\Unit\Observer;

use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\OfflinePayments\Model\Banktransfer;
use Magento\OfflinePayments\Model\Cashondelivery;
use Magento\OfflinePayments\Model\Checkmo;
use Magento\OfflinePayments\Observer\BeforeOrderPaymentSaveObserver;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\OfflinePayments\Observer\BeforeOrderPaymentSaveObserver
 */
class BeforeOrderPaymentSaveObserverTest extends TestCase
{
    private const STORE_ID = 1;

    /**
     * @var BeforeOrderPaymentSaveObserver
     */
    private $model;

    /**
     * @var Payment|MockObject
     */
    private $paymentMock;

    /**
     * @var Event|MockObject
     */
    private $eventMock;

    /**
     * @var Observer|MockObject
     */
    private $observerMock;

    /**
     * @var Order|MockObject
     */
    private $orderMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->paymentMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventMock = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->addMethods(['getPayment'])
            ->getMock();

        $this->eventMock->expects(self::once())
            ->method('getPayment')
            ->willReturn($this->paymentMock);

        $this->observerMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->observerMock->expects(self::once())
            ->method('getEvent')
            ->willReturn($this->eventMock);

        $this->orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderMock->method('getStoreId')
            ->willReturn(static::STORE_ID);

        $this->paymentMock->method('getOrder')
            ->willReturn($this->orderMock);

        $this->model = $objectManagerHelper->getObject(BeforeOrderPaymentSaveObserver::class);
    }

    /**
     * Checks a case when payment method is either bank transfer or cash on delivery
     * @param string $methodCode
     * @dataProvider dataProviderBeforeOrderPaymentSaveWithInstructions
     */
    public function testBeforeOrderPaymentSaveWithInstructions($methodCode)
    {
        $this->paymentMock->expects(self::once())
            ->method('getMethod')
            ->willReturn($methodCode);
        $this->paymentMock->expects(self::once())
            ->method('setAdditionalInformation')
            ->with('instructions', 'payment configuration');
        $method = $this->getMockBuilder(Banktransfer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $method->expects(self::once())
            ->method('getConfigData')
            ->with('instructions', static::STORE_ID)
            ->willReturn('payment configuration');
        $this->paymentMock->expects(self::once())
            ->method('getMethodInstance')
            ->willReturn($method);

        $this->model->execute($this->observerMock);
    }

    /**
     * Returns list of payment method codes.
     *
     * @return array
     */
    public static function dataProviderBeforeOrderPaymentSaveWithInstructions()
    {
        return [
            [Banktransfer::PAYMENT_METHOD_BANKTRANSFER_CODE],
            [Cashondelivery::PAYMENT_METHOD_CASHONDELIVERY_CODE],
        ];
    }

    /**
     * Checks a case when payment method is Check Money
     */
    public function testBeforeOrderPaymentSaveWithCheckmo()
    {
        $this->paymentMock->expects(self::exactly(2))
            ->method('getMethod')
            ->willReturn(Checkmo::PAYMENT_METHOD_CHECKMO_CODE);
        $this->paymentMock->expects(self::exactly(2))
            ->method('setAdditionalInformation')
            ->willReturnMap(
                [
                    ['payable_to', 'payable to', $this->paymentMock],
                    ['mailing_address', 'mailing address', $this->paymentMock],
                ]
            );

        $method = $this->getMockBuilder(Checkmo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $method->method('getConfigData')
            ->willReturnMap(
                [
                    ['payable_to', static::STORE_ID, 'payable to'],
                    ['mailing_address', static::STORE_ID, 'mailing address']
                ]
            );
        $this->paymentMock->expects(self::once())
            ->method('getMethodInstance')
            ->willReturn($method);
        $this->model->execute($this->observerMock);
    }

    /**
     * Checks a case when payment method is Check Money order and
     * payable person and mailing address do not specified.
     */
    public function testBeforeOrderPaymentSaveWithCheckmoWithoutConfig()
    {
        $this->paymentMock->expects(self::exactly(2))
            ->method('getMethod')
            ->willReturn(Checkmo::PAYMENT_METHOD_CHECKMO_CODE);
        $this->paymentMock->expects(self::never())
            ->method('setAdditionalInformation');

        $method = $this->getMockBuilder(Checkmo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $method->method('getConfigData')
            ->willReturnMap(
                [
                    ['payable_to', static::STORE_ID, null],
                    ['mailing_address', static::STORE_ID, null]
                ]
            );
        $this->paymentMock->expects(self::once())
            ->method('getMethodInstance')
            ->willReturn($method);
        $this->model->execute($this->observerMock);
    }

    /**
     * Checks a case with payment method not handled by observer
     */
    public function testBeforeOrderPaymentSaveWithOthers()
    {
        $this->paymentMock->expects(self::exactly(2))
            ->method('getMethod')
            ->willReturn('somepaymentmethod');
        $this->paymentMock->expects(self::never())
            ->method('setAdditionalInformation');

        $this->model->execute($this->observerMock);
    }

    /**
     * @param string $methodCode
     * @dataProvider dataProviderBeforeOrderPaymentSaveWithInstructions
     */
    public function testBeforeOrderPaymentSaveWithInstructionsAlreadySet($methodCode)
    {
        $this->paymentMock->method('getMethod')
            ->willReturn($methodCode);

        $this->paymentMock->expects(self::once())
            ->method('getAdditionalInformation')
            ->willReturn('Test');

        $this->paymentMock->expects(self::never())
            ->method('setAdditionalInformation');

        $this->model->execute($this->observerMock);
    }
}
