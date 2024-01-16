<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Payment\Transaction;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment\Transaction\Builder;
use Magento\Sales\Model\Order\Payment\Transaction\Repository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    /**
     * @var Repository|MockObject
     */
    protected $repositoryMock;

    /**
     * @var Order|MockObject
     */
    protected $orderMock;

    /**
     * @var Payment|MockObject
     */
    protected $paymentMock;

    /**
     * @var Builder
     */
    protected $builder;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->repositoryMock = $this->createMock(Repository::class);
        $this->paymentMock = $this->getMockBuilder(Payment::class)
            ->addMethods(['hasIsTransactionClosed', 'getIsTransactionClosed'])
            ->onlyMethods(['getId', 'getParentTransactionId', 'getShouldCloseParentTransaction'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderMock = $this->createMock(Order::class);
        $this->builder = $objectManager->getObject(
            Builder::class,
            ['transactionRepository' => $this->repositoryMock]
        );
    }

    /**
     * @param int $transactionId
     * @param int $orderId
     * @param int $paymentId
     * @param bool $failSafe
     * @param string $type
     * @param bool $isPaymentTransactionClosed
     * @param array $additionalInfo
     * @param bool $document
     * @param bool $isTransactionExists
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @dataProvider createDataProvider
     */
    public function testCreate(
        int $transactionId,
        int $orderId,
        int $paymentId,
        bool $failSafe,
        string $type,
        bool $isPaymentTransactionClosed,
        array $additionalInfo,
        bool $document,
        bool $isTransactionExists
    ): void {
        $parentTransactionId = '12';
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
            ->willReturnCallback(function (
                $arg1,
                $arg2,
                $arg3
            ) use (
                $transactionId,
                $paymentId,
                $orderId,
                $parentTransactionId,
                $parentTransaction,
                $transaction
            ) {
                if ($arg1 == $transactionId && $arg2 == $paymentId && $arg3 ==  $orderId) {
                    return $transaction;
                } elseif ($arg1 == $parentTransactionId && $arg2 == $paymentId && $arg3 ==  $orderId) {
                    return $parentTransaction;
                }
            });
        } else {
            $this->repositoryMock->method('getByTransactionId')
                ->willReturnCallback(function (
                    $arg1,
                    $arg2,
                    $arg3
                ) use (
                    $transactionId,
                    $paymentId,
                    $orderId,
                    $parentTransactionId,
                    $parentTransaction
                ) {
                    if ($arg1 == $transactionId && $arg2 == $paymentId && $arg3 ==  $orderId) {
                        return false;
                    } elseif ($arg1 == $parentTransactionId && $arg2 == $paymentId && $arg3 ==  $orderId) {
                        return $parentTransaction;
                    }
                });

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
     * @param MockObject $transaction
     * @param string $parentTransactionId
     * @param bool $shouldCloseParentTransaction
     * @param MockObject $parentTransaction
     * @param bool $parentTransactionIsClosed
     *
     * @return void
     */
    protected function expectsLinkWithParentTransaction(
        MockObject $transaction,
        string $parentTransactionId,
        bool $shouldCloseParentTransaction,
        MockObject $parentTransaction,
        bool $parentTransactionIsClosed
    ): void {
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
                $this->orderMock
                    ->method('addRelatedObject')
                    ->with($parentTransaction);
            }
        }
    }

    /**
     * @param int $orderId
     * @param int $paymentId
     *
     * @return MockObject
     */
    protected function expectTransaction(int $orderId, int $paymentId): MockObject
    {
        $newTransaction = $this->getMockBuilder(Transaction::class)
            ->addMethods(['loadByTxnId', 'setPayment'])
            ->onlyMethods(
                [
                    'getId',
                    'setOrderId',
                    'setPaymentId',
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
                    'setOrder',
                    'setIsClosed'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderMock->expects($this->atLeastOnce())->method('getId')->willReturn($orderId);
        $this->paymentMock->expects($this->atLeastOnce())->method('getId')->willReturn($paymentId);
        return $newTransaction;
    }

    /**
     * @param int $transactionId
     *
     * @return MockObject
     */
    protected function expectDocument(int $transactionId): MockObject
    {
        $document = $this->getMockBuilder(Order::class)
            ->addMethods(['setTransactionId'])
            ->disableOriginalConstructor()
            ->getMock();

        $document->expects($this->once())->method('setTransactionId')->with($transactionId);
        return $document;
    }

    /**
     * @param MockObject $newTransaction
     * @param string $type
     * @param bool $failSafe
     *
     * @return void
     */
    protected function expectSetPaymentObject(
        MockObject $newTransaction,
        string $type,
        bool $failSafe
    ): void {
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
     * @param MockObject $newTransaction
     *
     * @return void
     */
    protected function expectsIsPaymentTransactionClosed(
        bool $isPaymentTransactionClosed,
        MockObject $newTransaction
    ): void {
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
    public static function createDataProvider(): array
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
