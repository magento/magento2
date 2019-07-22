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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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
     * @var FilterBuilder|MockObject
     */
    private $filterBuilderMock;

    /**
     * @var SearchCriteriaBuilder|MockObject
     */
    private $searchCriteriaBuilderMock;

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

    protected function setUp()
    {
        // Simple mocks
        $this->paymentDOMock = $this->createMock(PaymentDataObject::class);
        $this->paymentMock = $this->createMock(Payment::class);
        $this->paymentDOMock->method('getPayment')
            ->willReturn($this->paymentMock);
        $this->commandMock = $this->createMock(GatewayCommand::class);
        $this->commandPoolMock = $this->createMock(CommandPoolInterface::class);
        $this->searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $this->transactionRepositoryMock = $this->createMock(TransactionRepositoryInterface::class);

        // The search criteria builder should return the criteria with the specified filters
        $this->filterBuilderMock = $this->createMock(FilterBuilder::class);
        // We aren't coupling the implementation to the test. The test only cares how the result is processed
        $this->filterBuilderMock->method('setField')
            ->willReturnSelf();
        $this->filterBuilderMock->method('setValue')
            ->willReturnSelf();
        $searchCriteria = new SearchCriteria();
        $this->searchCriteriaBuilderMock->method('addFilters')
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->method('create')
            ->willReturn($searchCriteria);
        // The transaction result can be customized per test to simulate different scenarios
        $this->transactionsResult = $this->createMock(TransactionSearchResultInterface::class);
        $this->transactionRepositoryMock->method('getList')
            ->with($searchCriteria)
            ->willReturn($this->transactionsResult);

        $this->strategyCommand = new CaptureStrategyCommand(
            $this->commandPoolMock,
            $this->transactionRepositoryMock,
            $this->filterBuilderMock,
            $this->searchCriteriaBuilderMock,
            new SubjectReader()
        );
    }

    public function testExecuteWillAuthorizeWhenNotAuthorizedAndNotCaptured()
    {
        $subject = ['payment' => $this->paymentDOMock];

        // Hasn't been authorized
        $this->paymentMock->method('getAuthorizationTransaction')
            ->willReturn(false);
        // Hasn't been captured
        $this->transactionsResult->method('getTotalCount')
            ->willReturn(0);
        // Assert authorize command was used
        $this->commandPoolMock->expects($this->once())
            ->method('get')
            ->with('sale')
            ->willReturn($this->commandMock);
        // Assert execute was called and with correct data
        $this->commandMock->expects($this->once())
            ->method('execute')
            ->with($subject);

        $this->strategyCommand->execute($subject);
        // Assertions are performed via mock expects above
    }

    public function testExecuteWillAuthorizeAndCaptureWhenAlreadyCaptured()
    {
        $subject = ['payment' => $this->paymentDOMock];

        // Already authorized
        $this->paymentMock->method('getAuthorizationTransaction')
            ->willReturn(true);
        // And already captured
        $this->transactionsResult->method('getTotalCount')
            ->willReturn(1);
        // Assert authorize command was used
        $this->commandPoolMock->expects($this->once())
            ->method('get')
            ->with('settle')
            ->willReturn($this->commandMock);
        // Assert execute was called and with correct data
        $this->commandMock->expects($this->once())
            ->method('execute')
            ->with($subject);

        $this->strategyCommand->execute($subject);
        // Assertions are performed via mock expects above
    }

    public function testExecuteWillCaptureWhenAlreadyAuthorizedButNotCaptured()
    {
        $subject = ['payment' => $this->paymentDOMock];

        // Was already authorized
        $this->paymentMock->method('getAuthorizationTransaction')
            ->willReturn(true);
        // But, hasn't been captured
        $this->transactionsResult->method('getTotalCount')
            ->willReturn(0);
        // Assert authorize command was used
        $this->commandPoolMock->expects($this->once())
            ->method('get')
            ->with('settle')
            ->willReturn($this->commandMock);
        // Assert execute was called and with correct data
        $this->commandMock->expects($this->once())
            ->method('execute')
            ->with($subject);

        $this->strategyCommand->execute($subject);
        // Assertions are performed via mock expects above
    }
}
