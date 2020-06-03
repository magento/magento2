<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Model\Billing;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Paypal\Model\Billing\Agreement;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AgreementTest extends TestCase
{
    /**
     * @var Agreement
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $paymentDataMock;

    /**
     * @var MockObject
     */
    protected $paymentMethodInstanceMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->paymentDataMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMethodInstance'])
            ->getMock();

        $this->paymentMethodInstanceMock = $this->getMockBuilder(AbstractMethod::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'setStore',
                'getCode',
                'getFormBlockType',
                'getTitle',
                'getStore',
                'initBillingAgreementToken',
                'getBillingAgreementTokenInfo',
                'placeBillingAgreement'
            ])
            ->getMock();

        $this->model = $objectManager->getObject(
            Agreement::class,
            ['paymentData' => $this->paymentDataMock]
        );
    }

    public function testImportOrderPaymentWithMethodCode()
    {
        $baData = [
            'billing_agreement_id' => 'B-5E3253653W103435Y',
            'method_code' => 'paypal_billing_agreement'
        ];

        $paymentMock = $this->importOrderPaymentCommonPart($baData);

        $paymentMock->expects($this->once())
            ->method('getMethodInstance')
            ->willReturn($this->paymentMethodInstanceMock);

        $this->paymentMethodInstanceMock->expects($this->once())
            ->method('getCode')
            ->willReturn($baData['method_code']);

        $this->paymentDataMock->expects($this->once())
            ->method('getMethodInstance')
            ->with($baData['method_code'])
            ->willReturn($this->paymentMethodInstanceMock);

        $this->assertSame($this->model, $this->model->importOrderPayment($paymentMock));
    }

    public function testImportOrderPaymentWithoutMethodCode()
    {
        $baData = [
            'billing_agreement_id' => 'B-5E3253653W103435Y'
        ];

        $paymentMock = $this->importOrderPaymentCommonPart($baData);

        $paymentMock->expects($this->exactly(2))
            ->method('getMethodInstance')
            ->willReturn($this->paymentMethodInstanceMock);

        $this->paymentMethodInstanceMock->expects($this->once())
            ->method('getCode')
            ->willReturn('paypal_billing_agreement');

        $this->assertSame($this->model, $this->model->importOrderPayment($paymentMock));
    }

    /**
     * @param $baData
     * @return Data|MockObject
     */
    private function importOrderPaymentCommonPart($baData)
    {
        $paymentMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBillingAgreementData', 'getMethodInstance', 'getOrder'])
            ->getMock();

        $storeId = null;
        $customerId = 2;

        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerId'])
            ->getMock();

        $order->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $paymentMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($order);

        $paymentMock->expects($this->once())
            ->method('getBillingAgreementData')
            ->willReturn($baData);

        $this->paymentMethodInstanceMock->expects($this->once())
            ->method('setStore')
            ->with($storeId);

        $this->paymentMethodInstanceMock->expects($this->once())
            ->method('getTitle')
            ->willReturn('some title');

        $this->paymentMethodInstanceMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeId);

        return $paymentMock;
    }

    public function testInitToken()
    {
        $this->initGetMethodInstance();

        $this->paymentMethodInstanceMock->expects($this->once())
            ->method('initBillingAgreementToken')
            ->with($this->model)
            ->willReturn($this->model);

        $url = 'http://dddd';
        $this->model->setRedirectUrl($url);
        $this->assertEquals($url, $this->model->initToken());
    }

    public function testVerifyToken()
    {
        $this->initGetMethodInstance();

        $this->paymentMethodInstanceMock->expects($this->once())
            ->method('getBillingAgreementTokenInfo')
            ->with($this->model)
            ->willReturn($this->model);

        $this->assertEquals($this->model, $this->model->verifyToken());
    }

    private function initGetMethodInstance()
    {
        $this->paymentDataMock->expects($this->once())
            ->method('getMethodInstance')
            ->willReturn($this->paymentMethodInstanceMock);

        $this->paymentMethodInstanceMock->expects($this->once())
            ->method('setStore');
    }
}
