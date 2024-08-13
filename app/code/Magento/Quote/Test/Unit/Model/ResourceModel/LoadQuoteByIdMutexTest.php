<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\ResourceModel;

use Exception;
use InvalidArgumentException;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\LoadQuoteByIdMutex;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResourceModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LoadQuoteByIdMutexTest extends TestCase
{

    /**
     * @var QuoteResourceModel|MockObject
     */
    private $quoteResourceModelMock;

    /**
     * @var QuoteFactory|MockObject
     */
    private $quoteFactoryMock;

    /**
     * @var LoadQuoteByIdMutex
     */
    private $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->quoteResourceModelMock = $this->createMock(QuoteResourceModel::class);
        $this->quoteFactoryMock = $this->createMock(QuoteFactory::class);
        $this->model = new LoadQuoteByIdMutex(
            $this->quoteResourceModelMock,
            $this->quoteFactoryMock
        );
    }

    public function testExecuteWithEmptyIdsThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Quote ids must be provided');

        $this->model->execute([], fn () => true);
    }

    public function testExecuteWithValidIdsExecutesCallableAndCommitsTransaction(): void
    {
        $maskedIds = [1, 2, 3];
        $result = 'callBackResult';
        $callableMock = $this->createMock(SerializerInterface::class);
        $connectionMock = $this->createMock(AdapterInterface::class);
        $quoteMock = $this->createMock(Quote::class);
        $selectMock = $this->createMock(Select::class);

        $this->quoteResourceModelMock->method('getConnection')->willReturn($connectionMock);
        $this->quoteResourceModelMock->method('getMainTable')->willReturn('quote_table');
        $this->quoteResourceModelMock->method('getIdFieldName')->willReturn('entity_id');
        $selectMock->expects($this->once())
            ->method('from')
            ->with('quote_table')
            ->willReturnSelf();
        $selectMock->expects($this->once())
            ->method('where')
            ->with('entity_id IN (1, 2, 3)')
            ->willReturnSelf();
        $selectMock->expects($this->once())
            ->method('forUpdate')
            ->with(true)
            ->willReturnSelf();
        $connectionMock->expects($this->once())
            ->method('beginTransaction');
        $connectionMock->method('select')->willReturn($selectMock);
        $connectionMock->expects($this->once())
            ->method('prepareSqlCondition')
            ->with('entity_id', ['in' => $maskedIds])
            ->willReturn('entity_id IN (1, 2, 3)');
        $connectionMock->expects($this->once())
            ->method('fetchAll')
            ->willReturn([['entity_id' => 1]]);
        $connectionMock->expects($this->once())
            ->method('commit');
        $this->quoteFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($quoteMock);
        $callableMock->expects($this->once())
            ->method('serialize')
            ->with([$quoteMock])
            ->willReturn($result);

        $this->assertEquals($result, $this->model->execute($maskedIds, $callableMock->serialize(...)));
    }

    public function testExecuteWithCallableThrowingExceptionRollsBackTransaction(): void
    {
        $maskedIds = [1, 2, 3];
        $connectionMock = $this->createMock(AdapterInterface::class);
        $quoteMock = $this->createMock(Quote::class);
        $selectMock = $this->createMock(Select::class);

        $this->quoteResourceModelMock->method('getConnection')->willReturn($connectionMock);
        $this->quoteResourceModelMock->method('getMainTable')->willReturn('quote_table');
        $this->quoteResourceModelMock->method('getIdFieldName')->willReturn('entity_id');
        $selectMock->expects($this->once())
            ->method('from')
            ->with('quote_table')
            ->willReturnSelf();
        $selectMock->expects($this->once())
            ->method('where')
            ->with('entity_id IN (1, 2, 3)')
            ->willReturnSelf();
        $selectMock->expects($this->once())
            ->method('forUpdate')
            ->with(true)
            ->willReturnSelf();
        $connectionMock->expects($this->once())
            ->method('beginTransaction');
        $connectionMock->method('select')->willReturn($selectMock);
        $connectionMock->expects($this->once())
            ->method('prepareSqlCondition')
            ->with('entity_id', ['in' => $maskedIds])
            ->willReturn('entity_id IN (1, 2, 3)');
        $connectionMock->expects($this->once())
            ->method('fetchAll')
            ->willReturn([['entity_id' => 1]]);
        $connectionMock->expects($this->once())
            ->method('rollBack');
        $this->quoteFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($quoteMock);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Callable exception');

        $this->model->execute($maskedIds, fn () => throw new Exception('Callable exception'));
    }
}
