<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\Order\Payment\Transaction;

use Magento\Sales\Model\Order\Payment\Transaction;

class BuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Payment\Transaction\Repository | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $repositoryMock;

    /**
     * @var \Magento\Sales\Model\Order | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderMock;

    /**
     * @var \Magento\Sales\Model\Order\Payment | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMock;

    /**
     * @var \Magento\Sales\Model\Order\Payment\Transaction\Builder
     */
    protected $builder;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->repositoryMock = $this->getMock(
            'Magento\Sales\Model\Order\Payment\Transaction\Repository',
            [],
            [],
            '',
            false
        );
        $this->paymentMock = $this->getMock(
            'Magento\Sales\Model\Order\Payment',
            [
                'hasIsTransactionClosed',
                'getIsTransactionClosed',
                'getId',
                'getParentTransactionId',
                'getShouldCloseParentTransaction'
            ],
            [],
            '',
            false
        );
        $this->orderMock = $this->getMock(
            'Magento\Sales\Model\Order',
            [],
            [],
            '',
            false
        );
        $this->builder = $objectManager->getObject(
            'Magento\Sales\Model\Order\Payment\Transaction\Builder',
            ['transactionRepository' => $this->repositoryMock]
        );
    }

    /**
     * @dataProvider createDataProvider
     * @param string|null $transactionId
     * @param int $orderId
     * @param int $paymentId
     * @param bool $failSafe
     * @param string $type
     * @param bool $isPaymentTransactionClosed
     * @param array $additionalInfo
     * @param bool $document
     * @param bool $isTransactionExists
     */
    public function testCreate(
        $transactionId,
        $orderId,
        $paymentId,
        $failSafe,
        $type,
        $isPaymentTransactionClosed,
        $additionalInfo,
        $document,
        $isTransactionExists
    ) {
        $parentTransactionId = "12";
        $shouldCloseParentTransaction = true;
        $parentTransactionIsClosed = false;
        if ($document) {
            $document = $this->expectDocument($transactionId);
        }

        $parentTransaction = $this->expectTransaction($orderId, $paymentId);
        $transaction = $this->expectTransaction($orderId, $paymentId);
        $transaction->expects($this->atLeastOnce())->method('getTxnId')->willReturn($transactionId);
        $transaction->expects($this->once())
            ->method('setPayment')
            ->withAnyParameters()
            ->willReturnSelf();
        $transaction->expects($this->once())
            ->method('setOrder')
            ->withAnyParameters()
            ->willReturnSelf();

        if ($isTransactionExists) {
            $this->repositoryMock->method('getByTransactionId')
                ->withConsecutive(
                    [$transactionId, $paymentId, $orderId],
                    [$parentTransactionId, $paymentId, $orderId]
                )->willReturnOnConsecutiveCalls(
                    $transaction,
                    $parentTransaction
                );
        } else {
            $this->repositoryMock->method('getByTransactionId')
                ->withConsecutive(
                    [$transactionId, $paymentId, $orderId],
                    [$parentTransactionId, $paymentId, $orderId]
                )->willReturnOnConsecutiveCalls(false, $parentTransaction);
            $this->repositoryMock->method('create')
                ->willReturn($transaction);
            $transaction->expects($this->once())->method('setTxnId')
                ->with($transactionId)
                ->willReturn($transaction);
        }
        $this->expectSetPaymentObject($transaction, $type, $failSafe);
        $this->expectsIsPaymentTransactionClosed($isPaymentTransactionClosed, $transaction);
        $this->expectsIsPaymentTransactionClosed($isPaymentTransactionClosed, $transaction);
        $this->expectSetPaymentObject($transaction, $type, $failSafe);
        $this->expectsLinkWithParentTransaction(
            $transaction,
            $parentTransactionId,
            $shouldCloseParentTransaction,
            $parentTransaction,
            $parentTransactionIsClosed
        );
        if ($additionalInfo) {
            $transaction->expects($this->exactly(count($additionalInfo)))->method('setAdditionalInformation');
        }

        $builder = $this->builder->setPayment($this->paymentMock)
            ->setOrder($this->orderMock)
            ->setAdditionalInformation($additionalInfo)
            ->setFailSafe($failSafe)
            ->setTransactionId($transactionId);
        if ($document) {
            $builder->setSalesDocument($document);
        }
        $this->assertSame($transaction, $builder->build($type));
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $transaction
     * @param string|null $parentTransactionId
     * @param bool $shouldCloseParentTransaction
     * @param \PHPUnit_Framework_MockObject_MockObject $parentTransaction
     * @param bool $parentTransactionIsClosed
     */
    protected function expectsLinkWithParentTransaction(
        $transaction,
        $parentTransactionId,
        $shouldCloseParentTransaction,
        $parentTransaction,
        $parentTransactionIsClosed
    ) {
        $this->paymentMock->method('getParentTransactionId')->willReturn($parentTransactionId);
        if ($parentTransactionId) {
            $transaction->expects($this->once())->method('setParentTxnId')->with($parentTransactionId);
            $this->paymentMock->expects($this->once())
                ->method('getShouldCloseParentTransaction')
                ->willReturn($shouldCloseParentTransaction);
            if ($shouldCloseParentTransaction) {
                $parentTransaction->expects($this->once())->method('getIsClosed')
                    ->willReturn($parentTransactionIsClosed);
                if (!$parentTransactionIsClosed) {
                    $parentTransaction->expects(
                        $this->once()
                    )->method('isFailsafe')
                        ->willReturnSelf();
                    $parentTransaction->expects(
                        $this->once()
                    )->method('close')
                        ->with(false)
                        ->willReturnSelf();
                }
                $this->orderMock->expects($this->at(2))->method('addRelatedObject')->with($parentTransaction);
            }
        }
    }

    /**
     * @param int $orderId
     * @param int $paymentId
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function expectTransaction($orderId, $paymentId)
    {
        $newTransaction = $this->getMock(
            'Magento\Sales\Model\Order\Payment\Transaction',
            [
                'getId',
                'setOrderId',
                'setPaymentId',
                'loadByTxnId',
                'setTxnId',
                'setTxnType',
                'isFailsafe',
                'getTxnId',
                'getHtmlTxnId',
                'getTxnType',
                'setAdditionalInformation',
                'setParentTxnId',
                'close',
                'getIsClosed',
                'setPayment',
                'setOrder'
            ],
            [],
            '',
            false
        );

        $this->orderMock->expects($this->atLeastOnce())->method('getId')->willReturn($orderId);
        $this->paymentMock->expects($this->atLeastOnce())->method('getId')->willReturn($paymentId);
        return $newTransaction;
    }

    /**
     * @param string $transactionId
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function expectDocument($transactionId)
    {
        $document = $this->getMock(
            'Magento\Sales\Model\Order',
            [
                'setTransactionId'
            ],
            [],
            '',
            false
        );

        $document->expects($this->once())->method('setTransactionId')->with($transactionId);
        return $document;
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $newTransaction
     * @param string $type
     * @param bool $failSafe
     */
    protected function expectSetPaymentObject($newTransaction, $type, $failSafe)
    {
        $newTransaction->expects($this->once())->method('setOrderId')
            ->willReturnSelf();
        $newTransaction->expects($this->once())->method('setPaymentId')
            ->willReturnSelf();
        $newTransaction->expects($this->once())->method('setTxnType')
            ->with($type)
            ->willReturnSelf();
        $newTransaction->expects($this->once())->method('isFailsafe')
            ->with($failSafe)
            ->willReturnSelf();
    }

    /**
     * @param bool $isPaymentTransactionClosed
     * @param \PHPUnit_Framework_MockObject_MockObject $newTransaction
     */
    protected function expectsIsPaymentTransactionClosed($isPaymentTransactionClosed, $newTransaction)
    {
        $this->paymentMock->expects($this->once())
            ->method('hasIsTransactionClosed')
            ->willReturn($isPaymentTransactionClosed);
        $newTransaction->expects($isPaymentTransactionClosed ? $this->once() : $this->never())
            ->method('setIsClosed')->with((int)$isPaymentTransactionClosed);
        $this->paymentMock->expects($isPaymentTransactionClosed ? $this->once() : $this->never())
            ->method('getIsTransactionClosed')
            ->willReturn($isPaymentTransactionClosed);
    }

    /**
     * @return array
     */
    public function createDataProvider()
    {
        return [
            'transactionNotExists' => [
                'transactionId' => 33,
                'orderId' => 19,
                'paymentId' => 15,
                'failSafe' => false,
                'type' => Transaction::TYPE_REFUND,
                'isPaymentTransactionClosed' => false,
                'additionalInfo' => ['some_key' => '332-ou'],
                'document' => true,
                'isTransactionExists' => false
            ],
            'transactionExists' => [
                'transactionId' => 33,
                'orderId' => 19,
                'paymentId' => 15,
                'failSafe' => false,
                'type' => Transaction::TYPE_REFUND,
                'isPaymentTransactionClosed' => false,
                'additionalInfo' => ['some_key' => '332-ou'],
                'document' => true,
                'isTransactionExists' => true
            ],
            'transactionWithoutDocument' => [
                'transactionId' => 33,
                'orderId' => 19,
                'paymentId' => 15,
                'failSafe' => false,
                'type' => Transaction::TYPE_REFUND,
                'isPaymentTransactionClosed' => false,
                'additionalInfo' => ['some_key' => '332-ou'],
                'document' => false,
                'isTransactionExists' => true
            ]
        ];
    }
}
