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
class AddBillingAgreementToSessionObserverTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\Paypal\Model\Billing\Agreement Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_agreementFactory;

    /**
     * @var \Magento\Checkout\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_checkoutSession;

    protected function setUp()
    {
        $this->_event = new \Magento\Framework\DataObject();

        $this->_observer = new \Magento\Framework\Event\Observer();
        $this->_observer->setEvent($this->_event);

        $this->_agreementFactory = $this->getMock(
            \Magento\Paypal\Model\Billing\AgreementFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->_checkoutSession = $this->getMock(\Magento\Checkout\Model\Session::class, [], [], '', false);
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
        $payment = $this->getMock(\Magento\Sales\Model\Order\Payment::class, [], [], '', false);
        $payment->expects(
            $this->once()
        )->method(
            '__call'
        )->with(
            'getBillingAgreementData'
        )->will(
            $this->returnValue(null)
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
        $agreement = $this->getMock(\Magento\Paypal\Model\Billing\Agreement::class, [], [], '', false);
        $agreement->expects($this->once())->method('isValid')->will($this->returnValue($isValid));
        $comment = $this->getMockForAbstractClass(
            \Magento\Framework\Model\AbstractModel::class,
            [],
            '',
            false,
            true,
            true,
            ['__wakeup']
        );
        $order = $this->getMock(\Magento\Sales\Model\Order::class, [], [], '', false);
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
        )->will(
            $this->returnValue($comment)
        );
        if ($isValid) {
            $agreement->expects(
                $this->any()
            )->method(
                '__call'
            )->with(
                'getReferenceId'
            )->will(
                $this->returnValue('agreement reference id')
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

        $payment = $this->getMock(\Magento\Sales\Model\Order\Payment::class, [], [], '', false);
        $payment->expects(
            $this->once()
        )->method(
            '__call'
        )->with(
            'getBillingAgreementData'
        )->will(
            $this->returnValue('not empty')
        );
        $payment->expects($this->once())->method('getOrder')->will($this->returnValue($order));
        $agreement->expects(
            $this->once()
        )->method(
            'importOrderPayment'
        )->with(
            $payment
        )->will(
            $this->returnValue($agreement)
        );
        $this->_event->setPayment($payment);
        $this->_agreementFactory->expects($this->once())->method('create')->will($this->returnValue($agreement));
        $this->_model->execute($this->_observer);
    }

    public function addBillingAgreementToSessionDataProvider()
    {
        return [[true], [false]];
    }
}
