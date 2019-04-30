<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Command;

use Magento\AuthorizenetAcceptjs\Gateway\Command\CaptureStrategyCommand;
use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Command\GatewayCommand;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Api\Data\TransactionSearchResultInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order\Payment;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Command\CaptureStrategyCommand
 */
class CaptureStrategyCommandTest extends TestCase
{
    /**
     * @var CaptureStrategyCommand
     */
    private $strategyCommand;

    /**
     * @var CommandPoolInterface|MockObject
     */
    private $commandPoolMock;

    /**
     * @var TransactionRepositoryInterface|MockObject
     */
    private $transactionRepositoryMock;

    /**
     * @var Payment|MockObject
     */
    private $paymentMock;

    /**
     * @var PaymentDataObject|MockObject
     */
    private $paymentDOMock;

    /**
     * @var GatewayCommand|MockObject
     */
    private $commandMock;

    /**
     * @var TransactionSearchResultInterface|MockObject
     */
    private $transactionsResult;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $searchCriteria = new SearchCriteria();
        $searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $filterBuilderMock = $this->createMock(FilterBuilder::class);

        $filterBuilderMock->method('setField')->willReturnSelf();
        $filterBuilderMock->method('setValue')->willReturnSelf();
        $searchCriteriaBuilderMock->method('addFilters')
            ->willReturnSelf();
        $searchCriteriaBuilderMock->method('create')
            ->willReturn($searchCriteria);

        $this->commandMock = $this->createMock(GatewayCommand::class);
        $this->commandPoolMock = $this->createMock(CommandPoolInterface::class);
        $this->transactionRepositoryMock = $this->createMock(TransactionRepositoryInterface::class);
        $this->paymentDOMock = $this->createMock(PaymentDataObject::class);
        $this->paymentMock = $this->createMock(Payment::class);
        $this->paymentDOMock->method('getPayment')->willReturn($this->paymentMock);

        // The transaction result can be customized per test to simulate different scenarios
        $this->transactionsResult = $this->createMock(TransactionSearchResultInterface::class);
        $this->transactionRepositoryMock->method('getList')
            ->with($searchCriteria)
            ->willReturn($this->transactionsResult);

        $this->strategyCommand = $objectManagerHelper->getObject(
            CaptureStrategyCommand::class,
            [
                'commandPool' => $this->commandPoolMock,
                'transactionRepository' => $this->transactionRepositoryMock,
                'filterBuilder' => $filterBuilderMock,
                'searchCriteriaBuilder' => $searchCriteriaBuilderMock,
                'subjectReader' => new SubjectReader(),
            ]
        );
    }

    /**
     * @dataProvider testExecuteDataProvider
     * @param bool $transaction
     * @param int $totalCount
     * @param string $commandCode
     *
     * @return void
     */
    public function testExecute(bool $transaction, int $totalCount, string $commandCode)
    {
        $subject = ['payment' => $this->paymentDOMock];

        // Hasn't been authorized
        $this->paymentMock->method('getAuthorizationTransaction')
            ->willReturn($transaction);
        // Hasn't been captured
        $this->transactionsResult->method('getTotalCount')
            ->willReturn($totalCount);
        // Assert authorize command was used
        $this->commandPoolMock->expects($this->once())
            ->method('get')
            ->with($commandCode)
            ->willReturn($this->commandMock);
        // Assert execute was called and with correct data
        $this->commandMock->expects($this->once())
            ->method('execute')
            ->with($subject);

        $this->strategyCommand->execute($subject);
    }

    /**
     * @return array
     */
    public function testExecuteDataProvider(): array
    {
        return [
            [false, 0, 'sale'],
            [true, 1, 'settle'],
            [true, 0,'settle'],
        ];
    }
}
