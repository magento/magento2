<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Observer;

use Magento\Framework\TestFramework\Unit\Matcher\MethodInvokedAtIndex;

/**
 * Class AddBillingAgreementToSessionObserverTest
 */
class AddBillingAgreementToSessionObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Paypal\Observer\AddBillingAgreementToSessionObserver
     */
    protected $_model;

    /**
     * @var \Magento\Framework\Event\Observer
     */
    protected $_observer;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_event;

    /**
     * @var \Magento\Paypal\Model\Billing\Agreement Factory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_agreementFactory;

    /**
     * @var \Magento\Checkout\Model\Session|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_checkoutSession;

    protected function setUp(): void
    {
        $this->_event = new \Magento\Framework\DataObject();

        $this->_observer = new \Magento\Framework\Event\Observer();
        $this->_observer->setEvent($this->_event);

        $this->_agreementFactory = $this->createPartialMock(
            \Magento\Paypal\Model\Billing\AgreementFactory::class,
            ['create']
        );
        $this->_checkoutSession = $this->createMock(\Magento\Checkout\Model\Session::class);
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_model = $objectManagerHelper->getObject(
            \Magento\Paypal\Observer\AddBillingAgreementToSessionObserver::class,
            [
                'agreementFactory' => $this->_agreementFactory,
                'checkoutSession' => $this->_checkoutSession,
            ]
        );
    }

    public function testAddBillingAgreementToSessionNoData()
    {
        $payment = $this->createMock(\Magento\Sales\Model\Order\Payment::class);
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
        $agreement = $this->createMock(\Magento\Paypal\Model\Billing\Agreement::class);
        $agreement->expects($this->once())->method('isValid')->willReturn($isValid);
        $comment = $this->getMockForAbstractClass(
            \Magento\Framework\Model\AbstractModel::class,
            [],
            '',
            false,
            true,
            true,
            ['__wakeup']
        );
        $order = $this->createMock(\Magento\Sales\Model\Order::class);
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

        $payment = $this->createMock(\Magento\Sales\Model\Order\Payment::class);
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
    public function addBillingAgreementToSessionDataProvider()
    {
        return [[true], [false]];
    }
}
