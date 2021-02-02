<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model\Method;

class AgreementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_helper;

    /**
     * @var \Magento\Paypal\Model\Method\Agreement
     */
    protected $_model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_apiNvpMock;

    protected function setUp(): void
    {
        $this->_helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $paypalConfigMock = $this->getMockBuilder(
            \Magento\Paypal\Model\Config::class
        )->disableOriginalConstructor()->setMethods(
            []
        )->getMock();
        $this->_apiNvpMock = $this->getMockBuilder(
            \Magento\Paypal\Model\Api\Nvp::class
        )->disableOriginalConstructor()->setMethods(
            ['callDoReferenceTransaction', 'callGetTransactionDetails']
        )->getMock();
        $proMock = $this->getMockBuilder(
            \Magento\Paypal\Model\Pro::class
        )->setMethods(
            ['getApi', 'setMethod', 'getConfig', 'importPaymentInfo']
        )->disableOriginalConstructor()->getMock();
        $proMock->expects($this->any())->method('getApi')->willReturn($this->_apiNvpMock);
        $proMock->expects($this->any())->method('getConfig')->willReturn($paypalConfigMock);

        $billingAgreementMock = $this->getMockBuilder(
            \Magento\Paypal\Model\Billing\Agreement::class
        )->disableOriginalConstructor()->setMethods(
            ['load', '__wakeup']
        )->getMock();
        $billingAgreementMock->expects($this->any())->method('load')->willReturn($billingAgreementMock);

        $agreementFactoryMock = $this->getMockBuilder(
            \Magento\Paypal\Model\Billing\AgreementFactory::class
        )->disableOriginalConstructor()->setMethods(
            ['create']
        )->getMock();
        $agreementFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->willReturn(
            $billingAgreementMock
        );

        $cartMock = $this->getMockBuilder(\Magento\Paypal\Model\Cart::class)->disableOriginalConstructor()->getMock();
        $cartFactoryMock = $this->getMockBuilder(
            \Magento\Paypal\Model\CartFactory::class
        )->disableOriginalConstructor()->setMethods(
            ['create']
        )->getMock();
        $cartFactoryMock->expects($this->any())->method('create')->willReturn($cartMock);

        $arguments = [
            'agreementFactory' => $agreementFactoryMock,
            'cartFactory' => $cartFactoryMock,
            'data' => [$proMock],
        ];

        $this->_model = $this->_helper->getObject(\Magento\Paypal\Model\Method\Agreement::class, $arguments);
    }

    public function testAuthorizeWithBaseCurrency()
    {
        $payment = $this->getMockBuilder(
            \Magento\Sales\Model\Order\Payment::class
        )->disableOriginalConstructor()->setMethods(
            ['__wakeup']
        )->getMock();
        $order = $this->getMockBuilder(
            \Magento\Sales\Model\Order::class
        )->disableOriginalConstructor()->setMethods(
            ['__wakeup']
        )->getMock();
        $order->setBaseCurrencyCode('USD');
        $payment->setOrder($order);

        $this->_model->authorize($payment, 10.00);
        $this->assertEquals($order->getBaseCurrencyCode(), $this->_apiNvpMock->getCurrencyCode());
    }
}
