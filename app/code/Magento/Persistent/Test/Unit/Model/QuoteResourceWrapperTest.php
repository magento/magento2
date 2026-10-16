<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Persistent\Model\QuoteResourceWrapper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class QuoteResourceWrapperTest extends TestCase
{
    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var QuoteResourceWrapper
     */
    private $model;

    /**
     * @var Select|MockObject
     */
    private $selectMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->resourceConnectionMock = $this->createMock(ResourceConnection::class);
        $this->connectionMock = $this->createMock(AdapterInterface::class);
        $this->selectMock = $this->createMock(Select::class);

        $this->model = new QuoteResourceWrapper($this->resourceConnectionMock);

        $this->resourceConnectionMock->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->connectionMock->method('select')
            ->willReturn($this->selectMock);
    }

    /**
     * Null quote id short-circuits without any DB access.
     */
    public function testGetFlagsWithNullQuoteId(): void
    {
        $this->connectionMock->expects($this->never())->method('fetchRow');

        $this->assertSame(
            ['is_active' => false, 'is_persistent' => false],
            $this->model->getFlags(null)
        );
    }

    /**
     * Both flags are read in a single row fetch.
     */
    public function testGetFlagsReadsBothColumnsInOneQuery(): void
    {
        $quoteId = 123;
        $tableName = 'quote';

        $this->resourceConnectionMock->expects($this->once())
            ->method('getTableName')
            ->with('quote')
            ->willReturn($tableName);

        $this->selectMock->expects($this->once())
            ->method('from')
            ->with($tableName, ['is_active', 'is_persistent'])
            ->willReturnSelf();

        $this->selectMock->expects($this->once())
            ->method('where')
            ->with('entity_id = ?', $quoteId)
            ->willReturnSelf();

        $this->connectionMock->expects($this->once())
            ->method('fetchRow')
            ->with($this->selectMock)
            ->willReturn(['is_active' => '1', 'is_persistent' => '0']);

        $this->assertSame(
            ['is_active' => true, 'is_persistent' => false],
            $this->model->getFlags($quoteId)
        );
    }

    /**
     * A missing quote row yields false for both flags.
     */
    public function testGetFlagsWithMissingRow(): void
    {
        $quoteId = 123;

        $this->resourceConnectionMock->expects($this->once())
            ->method('getTableName')
            ->willReturn('quote');
        $this->selectMock->expects($this->once())->method('from')->willReturnSelf();
        $this->selectMock->expects($this->once())->method('where')->willReturnSelf();
        $this->connectionMock->expects($this->once())
            ->method('fetchRow')
            ->willReturn(false);

        $this->assertSame(
            ['is_active' => false, 'is_persistent' => false],
            $this->model->getFlags($quoteId)
        );
    }

    /**
     * Values coming back from the database are cast to boolean.
     */
    public function testGetFlagsTypeCasting(): void
    {
        $quoteId = 123;

        $this->resourceConnectionMock->expects($this->once())
            ->method('getTableName')
            ->willReturn('quote');
        $this->selectMock->expects($this->once())->method('from')->willReturnSelf();
        $this->selectMock->expects($this->once())->method('where')->willReturnSelf();
        $this->connectionMock->expects($this->once())
            ->method('fetchRow')
            ->willReturn(['is_active' => '', 'is_persistent' => '1']);

        $this->assertSame(
            ['is_active' => false, 'is_persistent' => true],
            $this->model->getFlags($quoteId)
        );
    }
}
