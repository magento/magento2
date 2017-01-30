<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\Unit\Model\Plugin\Sales\Order;

use Magento\Braintree\Model\PaymentMethod;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\CollectionFactory as TransactionCollectionFactory;

/**
 * Class PaymentPluginTest
 *
 */
class PaymentPluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Braintree\Model\Plugin\Sales\Order\PaymentPlugin
     */
    protected $model;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    /**
     * @var TransactionCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionCollectionFactoryMock;

    /**
     * @var \Magento\Braintree\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    protected function setUp()
    {
        $this->registryMock = $this->getMockBuilder('\Magento\Framework\Registry')
            ->disableOriginalConstructor()
            ->getMock();
        $this->helperMock = $this->getMockBuilder('\Magento\Braintree\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();
        $this->transactionCollectionFactoryMock = $this->getMockBuilder(
            'Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\CollectionFactory'
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            'Magento\Braintree\Model\Plugin\Sales\Order\PaymentPlugin',
            [
                'registry' => $this->registryMock,
                'salesTransactionCollectionFactory' => $this->transactionCollectionFactoryMock,
                'paymentHelper' => $this->helperMock,
            ]
        );
    }

    public function testAroundGetAuthorizationTransactionNotPaymentMethod()
    {
        $result = 'result';
        $proceed = function () use ($result) {
            return $result;
        };

        $methodInstanceMock = $this->getMock('\Magento\Payment\Model\MethodInterface');
        $methodInstanceMock->expects($this->once())
            ->method('getCode')
            ->willReturn('PayPal');

        $payment = $this->getMockBuilder('\Magento\Sales\Model\Order\Payment')
            ->disableOriginalConstructor()
            ->getMock();
        $payment->expects($this->once())
            ->method('getMethodInstance')
            ->willReturn($methodInstanceMock);

        $this->assertEquals($result, $this->model->aroundGetAuthorizationTransaction($payment, $proceed));
    }

    public function testAroundGetAuthorizationTransaction()
    {
        $result = 'result';
        $transactionId = 're45gf';
        $transaction = 'invoice_transaction';

        $proceed = function () use ($result) {
            return $result;
        };

        $methodInstanceMock = $this->getMock('\Magento\Payment\Model\MethodInterface');
        $methodInstanceMock->expects($this->once())
            ->method('getCode')
            ->willReturn(PaymentMethod::METHOD_CODE);

        $invoiceMock = $this->getMockBuilder('\Magento\Sales\Model\Order\Invoice')
            ->disableOriginalConstructor()
            ->getMock();

        $invoiceMock->expects($this->once())
            ->method('getId')
            ->willReturn(1004);
        $invoiceMock->expects($this->once())
            ->method('getTransactionId')
            ->willReturn($transactionId);
        $this->helperMock->expects($this->once())
            ->method('clearTransactionId')
            ->with($transactionId)
            ->willReturn($transactionId);

        $collectionMock =
            $this->getMockBuilder('\Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $this->transactionCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);
        $collectionMock->expects($this->once())
            ->method('addFieldToFilter')
            ->with('txn_id', ['eq' => $transactionId])
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('getSize')
            ->willReturn(1);
        $collectionMock->expects($this->once())
            ->method('getFirstItem')
            ->willReturn($transaction);

        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with('current_invoice')
            ->willReturn($invoiceMock);

        $payment = $this->getMockBuilder('\Magento\Sales\Model\Order\Payment')
            ->disableOriginalConstructor()
            ->getMock();
        $payment->expects($this->once())
            ->method('getMethodInstance')
            ->willReturn($methodInstanceMock);

        $this->assertEquals($transaction, $this->model->aroundGetAuthorizationTransaction($payment, $proceed));
    }
    public function testAroundGetAuthorizationTransactionNoInvoiceTransaction()
    {
        $result = 'result';
        $transactionId = 're45gf';
        $transaction = 'invoice_transaction';

        $proceed = function () use ($result) {
            return $result;
        };

        $methodInstanceMock = $this->getMock('\Magento\Payment\Model\MethodInterface');
        $methodInstanceMock->expects($this->once())
            ->method('getCode')
            ->willReturn(PaymentMethod::METHOD_CODE);

        $invoiceMock = $this->getMockBuilder('\Magento\Sales\Model\Order\Invoice')
            ->disableOriginalConstructor()
            ->getMock();

        $invoiceMock->expects($this->once())
            ->method('getId')
            ->willReturn(1004);
        $invoiceMock->expects($this->once())
            ->method('getTransactionId')
            ->willReturn($transactionId);
        $this->helperMock->expects($this->once())
            ->method('clearTransactionId')
            ->with($transactionId)
            ->willReturn($transactionId);

        $collectionMock =
            $this->getMockBuilder('\Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $this->transactionCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);
        $collectionMock->expects($this->once())
            ->method('addFieldToFilter')
            ->with('txn_id', ['eq' => $transactionId])
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('getSize')
            ->willReturn(0);
        $collectionMock->expects($this->never())
            ->method('getFirstItem')
            ->willReturn($transaction);

        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with('current_invoice')
            ->willReturn($invoiceMock);

        $payment = $this->getMockBuilder('\Magento\Sales\Model\Order\Payment')
            ->disableOriginalConstructor()
            ->getMock();
        $payment->expects($this->once())
            ->method('getMethodInstance')
            ->willReturn($methodInstanceMock);

        $this->assertEquals($result, $this->model->aroundGetAuthorizationTransaction($payment, $proceed));
    }
}
