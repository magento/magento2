<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Observer;

use Magento\Checkout\Model\Session;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\TestFramework\Unit\Matcher\MethodInvokedAtIndex;
use Magento\Paypal\Model\Billing\Agreement;
use Magento\Paypal\Model\Billing\AgreementFactory;
use Magento\Paypal\Observer\AddBillingAgreementToSessionObserver;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AddBillingAgreementToSessionObserverTest extends TestCase
{
    /**
     * @var AddBillingAgreementToSessionObserver
     */
    protected $_model;

    /**
     * @var Observer
     */
    protected $_observer;

    /**
     * @var DataObject
     */
    protected $_event;

    /**
     * @var AgreementFactory|MockObject
     */
    protected $_agreementFactory;

    /**
     * @var Session|MockObject
     */
    protected $_checkoutSession;

    protected function setUp(): void
    {
        $this->_event = new DataObject();

        $this->_observer = new Observer();
        $this->_observer->setEvent($this->_event);

        $this->_agreementFactory = $this->createPartialMock(
            AgreementFactory::class,
            ['create']
        );
        $this->_checkoutSession = $this->createMock(Session::class);
        $objectManagerHelper = new ObjectManager($this);
        $this->_model = $objectManagerHelper->getObject(
            AddBillingAgreementToSessionObserver::class,
            [
                'agreementFactory' => $this->_agreementFactory,
                'checkoutSession' => $this->_checkoutSession,
            ]
        );
    }

    public function testAddBillingAgreementToSessionNoData()
    {
        $payment = $this->createMock(Payment::class);
        $payment->expects(
            $this->once()
        )->method(
            '__call'
        )->with(
            'getBillingAgreementData'
        )->willReturn(
            null
        );
        $this->_event->setPayment($payment);
        $this->_agreementFactory->expects($this->never())->method('create');
        $this->_checkoutSession->expects($this->once())->method('__call')->with('unsLastBillingAgreementReferenceId');
        $this->_model->execute($this->_observer);
    }

    /**
     * @param bool $isValid
     * @dataProvider addBillingAgreementToSessionDataProvider
     */
    public function testAddBillingAgreementToSession($isValid)
    {
        $agreement = $this->createMock(Agreement::class);
        $agreement->expects($this->once())->method('isValid')->willReturn($isValid);
        $comment = $this->getMockForAbstractClass(
            AbstractModel::class,
            [],
            '',
            false,
            true,
            true,
            ['__wakeup']
        );
        $order = $this->createMock(Order::class);
        $order->expects(
            $this->once()
        )->method(
            'addStatusHistoryComment'
        )->with(
            $isValid ? __(
                'Created billing agreement #%1.',
                'agreement reference id'
            ) : __(
                'We can\'t create a billing agreement for this order.'
            )
        )->willReturn(
            $comment
        );
        if ($isValid) {
            $agreement->expects(
                $this->any()
            )->method(
                '__call'
            )->with(
                'getReferenceId'
            )->willReturn(
                'agreement reference id'
            );
            $agreement->expects($this->once())->method('addOrderRelation')->with($order);
            $order->expects(new MethodInvokedAtIndex(0))->method('addRelatedObject')->with($agreement);
            $this->_checkoutSession->expects(
                $this->once()
            )->method(
                '__call'
            )->with(
                'setLastBillingAgreementReferenceId',
                ['agreement reference id']
            );
        } else {
            $this->_checkoutSession->expects(
                $this->once()
            )->method(
                '__call'
            )->with(
                'unsLastBillingAgreementReferenceId'
            );
            $agreement->expects($this->never())->method('__call');
        }
        $order->expects(new MethodInvokedAtIndex($isValid ? 1 : 0))->method('addRelatedObject')->with($comment);

        $payment = $this->createMock(Payment::class);
        $payment->expects(
            $this->once()
        )->method(
            '__call'
        )->with(
            'getBillingAgreementData'
        )->willReturn(
            'not empty'
        );
        $payment->expects($this->once())->method('getOrder')->willReturn($order);
        $agreement->expects(
            $this->once()
        )->method(
            'importOrderPayment'
        )->with(
            $payment
        )->willReturn(
            $agreement
        );
        $this->_event->setPayment($payment);
        $this->_agreementFactory->expects($this->once())->method('create')->willReturn($agreement);
        $this->_model->execute($this->_observer);
    }

    /**
     * @return array
     */
    public static function addBillingAgreementToSessionDataProvider()
    {
        return [[true], [false]];
    }
}
