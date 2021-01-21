<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\Order\Payment\Operations;

use Magento\Framework\ObjectManager\ObjectManager;
use Magento\Payment\Model\Method;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class CaptureOperationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $transactionManager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventManager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $transactionBuilder;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $stateCommand;

    /**
     * @var \Magento\Sales\Model\Order\Payment\Operations\CaptureOperation
     */
    protected $model;

    protected function setUp(): void
    {
        $transactionClass = \Magento\Sales\Model\Order\Payment\Transaction\ManagerInterface::class;
        $transactionBuilderClass = \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface::class;
        $this->transactionManager = $this->getMockBuilder($transactionClass)
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventManager = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->transactionBuilder = $this->getMockBuilder($transactionBuilderClass)
            ->disableOriginalConstructor()
            ->getMock();
        $this->stateCommand = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment\State\CommandInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $objectManagerHelper->getObject(
            \Magento\Sales\Model\Order\Payment\Operations\CaptureOperation::class,
            [
                'transactionManager' => $this->transactionManager,
                'eventManager' => $this->eventManager,
                'transactionBuilder' => $this->transactionBuilder,
                'stateCommand' => $this->stateCommand
            ]
        );
    }

    public function testCapture()
    {
        $baseGrandTotal = 10;

        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $paymentMethod = $this->getMockBuilder(\Magento\Payment\Model\MethodInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderPayment = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderPayment->expects($this->any())
            ->method('formatAmount')
            ->with($baseGrandTotal)
            ->willReturnArgument(0);
        $orderPayment->expects($this->any())
            ->method('getOrder')
            ->willReturn($order);
        $orderPayment->expects($this->any())
            ->method('getMethodInstance')
            ->willReturn($paymentMethod);
        $orderPayment->expects($this->once())
            ->method('getIsTransactionPending')
            ->willReturn(true);
        $orderPayment->expects($this->once())
            ->method('getTransactionAdditionalInfo')
            ->willReturn([]);

        $paymentMethod->expects($this->once())
            ->method('capture')
            ->with($orderPayment, $baseGrandTotal);

        $this->transactionBuilder->expects($this->once())
            ->method('setPayment')
            ->with($orderPayment)
            ->willReturnSelf();

        $invoice = $this->getMockBuilder(\Magento\Sales\Model\Order\Invoice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $invoice->expects($this->any())
            ->method('getBaseGrandTotal')
            ->willReturn($baseGrandTotal);

        $this->model->capture($orderPayment, $invoice);
    }
}
