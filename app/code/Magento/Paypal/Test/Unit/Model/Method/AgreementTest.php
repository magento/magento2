<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

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

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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
        )->disableOriginalConstructor()
            ->onlyMethods(
                []
            )->getMock();
        $this->_apiNvpMock = $this->getMockBuilder(
            Nvp::class
        )->disableOriginalConstructor()
            ->onlyMethods(
                ['callDoReferenceTransaction', 'callGetTransactionDetails']
            )->getMock();
        $proMock = $this->getMockBuilder(
            Pro::class
        )->onlyMethods(
            ['getApi', 'setMethod', 'getConfig', 'importPaymentInfo']
        )->disableOriginalConstructor()
            ->getMock();
        $proMock->expects($this->any())->method('getApi')->willReturn($this->_apiNvpMock);
        $proMock->expects($this->any())->method('getConfig')->willReturn($paypalConfigMock);

        $billingAgreementMock = $this->getMockBuilder(
            \Magento\Paypal\Model\Billing\Agreement::class
        )->disableOriginalConstructor()
            ->onlyMethods(
                ['load', '__wakeup']
            )->getMock();
        $billingAgreementMock->expects($this->any())->method('load')->willReturn($billingAgreementMock);

        $agreementFactoryMock = $this->getMockBuilder(
            AgreementFactory::class
        )->disableOriginalConstructor()
            ->onlyMethods(
                ['create']
            )->getMock();
        $agreementFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->willReturn(
            $billingAgreementMock
        );

        $cartMock = $this->getMockBuilder(Cart::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cartFactoryMock = $this->getMockBuilder(
            CartFactory::class
        )->disableOriginalConstructor()
            ->onlyMethods(
                ['create']
            )->getMock();
        $cartFactoryMock->expects($this->any())->method('create')->willReturn($cartMock);

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
        )->disableOriginalConstructor()
            ->onlyMethods(
                ['__wakeup']
            )->getMock();
        $order = $this->getMockBuilder(
            Order::class
        )->disableOriginalConstructor()
            ->onlyMethods(
                ['__wakeup']
            )->getMock();
        $order->setBaseCurrencyCode('USD');
        $payment->setOrder($order);

        $this->_model->authorize($payment, 10.00);
        $this->assertEquals($order->getBaseCurrencyCode(), $this->_apiNvpMock->getCurrencyCode());
    }
}
