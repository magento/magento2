<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
        $this->_helper->prepareObjectManager();

        $paypalConfigMock = $this->createMock(Config::class);
        $this->_apiNvpMock = $this->createPartialMock(
            Nvp::class,
            ['callDoReferenceTransaction', 'callGetTransactionDetails']
        );
        $proMock = $this->createMock(Pro::class);
        $proMock->expects($this->any())->method('getApi')->willReturn($this->_apiNvpMock);
        $proMock->expects($this->any())->method('getConfig')->willReturn($paypalConfigMock);

        $billingAgreementMock = $this->createMock(\Magento\Paypal\Model\Billing\Agreement::class);
        $billingAgreementMock->expects($this->any())->method('load')->willReturn($billingAgreementMock);

        $agreementFactoryMock = $this->createMock(AgreementFactory::class);
        $agreementFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->willReturn(
            $billingAgreementMock
        );

        $cartMock = $this->createMock(Cart::class);
        $cartFactoryMock = $this->createMock(CartFactory::class);
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
        $payment = $this->createMock(Payment::class);
        $order = $this->createMock(Order::class);
        
        $order->expects($this->any())
            ->method('getBaseCurrencyCode')
            ->willReturn('USD');
        
        $payment->expects($this->any())
            ->method('getOrder')
            ->willReturn($order);
        
        $payment->expects($this->any())
            ->method('setTransactionId')
            ->willReturnSelf();
        
        $payment->expects($this->any())
            ->method('setIsTransactionClosed')
            ->willReturnSelf();
        
        $payment->expects($this->any())
            ->method('getAdditionalInformation')
            ->willReturn('reference_id');

        $this->_model->authorize($payment, 10.00);
        $this->assertEquals('USD', $this->_apiNvpMock->getCurrencyCode());
    }
}
