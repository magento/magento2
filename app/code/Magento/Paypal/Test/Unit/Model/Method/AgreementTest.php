<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model\Method;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Paypal\Model\Api\Nvp;
use Magento\Paypal\Model\Billing\AgreementFactory;
use Magento\Paypal\Model\Cart;
use Magento\Paypal\Model\CartFactory;
use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\Method\Agreement;
use Magento\Paypal\Model\Pro;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AgreementTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $_helper;

    /**
     * @var Agreement
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_apiNvpMock;

    protected function setUp(): void
    {
        $this->_helper = new ObjectManager($this);

        $paypalConfigMock = $this->getMockBuilder(
            Config::class
        )->disableOriginalConstructor()->setMethods(
            []
        )->getMock();
        $this->_apiNvpMock = $this->getMockBuilder(
            Nvp::class
        )->disableOriginalConstructor()->setMethods(
            ['callDoReferenceTransaction', 'callGetTransactionDetails']
        )->getMock();
        $proMock = $this->getMockBuilder(
            Pro::class
        )->setMethods(
            ['getApi', 'setMethod', 'getConfig', 'importPaymentInfo']
        )->disableOriginalConstructor()->getMock();
        $proMock->expects($this->any())->method('getApi')->will($this->returnValue($this->_apiNvpMock));
        $proMock->expects($this->any())->method('getConfig')->will($this->returnValue($paypalConfigMock));

        $billingAgreementMock = $this->getMockBuilder(
            \Magento\Paypal\Model\Billing\Agreement::class
        )->disableOriginalConstructor()->setMethods(
            ['load', '__wakeup']
        )->getMock();
        $billingAgreementMock->expects($this->any())->method('load')->will($this->returnValue($billingAgreementMock));

        $agreementFactoryMock = $this->getMockBuilder(
            AgreementFactory::class
        )->disableOriginalConstructor()->setMethods(
            ['create']
        )->getMock();
        $agreementFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($billingAgreementMock)
        );

        $cartMock = $this->getMockBuilder(Cart::class)->disableOriginalConstructor()->getMock();
        $cartFactoryMock = $this->getMockBuilder(
            CartFactory::class
        )->disableOriginalConstructor()->setMethods(
            ['create']
        )->getMock();
        $cartFactoryMock->expects($this->any())->method('create')->will($this->returnValue($cartMock));

        $arguments = [
            'agreementFactory' => $agreementFactoryMock,
            'cartFactory' => $cartFactoryMock,
            'data' => [$proMock],
        ];

        $this->_model = $this->_helper->getObject(Agreement::class, $arguments);
    }

    public function testAuthorizeWithBaseCurrency()
    {
        $payment = $this->getMockBuilder(
            Payment::class
        )->disableOriginalConstructor()->setMethods(
            ['__wakeup']
        )->getMock();
        $order = $this->getMockBuilder(
            Order::class
        )->disableOriginalConstructor()->setMethods(
            ['__wakeup']
        )->getMock();
        $order->setBaseCurrencyCode('USD');
        $payment->setOrder($order);

        $this->_model->authorize($payment, 10.00);
        $this->assertEquals($order->getBaseCurrencyCode(), $this->_apiNvpMock->getCurrencyCode());
    }
}
