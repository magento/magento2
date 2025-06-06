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
        $this->connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $this->selectMock = $this->createMock(Select::class);

        $this->model = new QuoteResourceWrapper($this->resourceConnectionMock);

        $this->resourceConnectionMock->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->connectionMock->method('select')
            ->willReturn($this->selectMock);
    }

    /**
     * Test isActive with null quote ID
     */
    public function testIsActiveWithNullQuoteId(): void
    {
        $this->assertFalse($this->model->isActive(null));
    }

    /**
     * Test isActive with active quote
     */
    public function testIsActiveWithActiveQuote(): void
    {
        $quoteId = 123;
        $tableName = 'quote';

        $this->resourceConnectionMock->expects($this->once())
            ->method('getTableName')
            ->with('quote')
            ->willReturn($tableName);

        $this->selectMock->expects($this->once())
            ->method('from')
            ->with($tableName, 'is_active')
            ->willReturnSelf();

        $this->selectMock->expects($this->once())
            ->method('where')
            ->with('entity_id = ?', $quoteId)
            ->willReturnSelf();

        $this->connectionMock->expects($this->once())
            ->method('fetchOne')
            ->with($this->selectMock)
            ->willReturn('1');

        $this->assertTrue($this->model->isActive($quoteId));
    }

    /**
     * Test isActive with inactive quote
     */
    public function testIsActiveWithInactiveQuote(): void
    {
        $quoteId = 123;
        $tableName = 'quote';

        $this->resourceConnectionMock->expects($this->once())
            ->method('getTableName')
            ->with('quote')
            ->willReturn($tableName);

        $this->selectMock->expects($this->once())
            ->method('from')
            ->with($tableName, 'is_active')
            ->willReturnSelf();

        $this->selectMock->expects($this->once())
            ->method('where')
            ->with('entity_id = ?', $quoteId)
            ->willReturnSelf();

        $this->connectionMock->expects($this->once())
            ->method('fetchOne')
            ->with($this->selectMock)
            ->willReturn('0');

        $this->assertFalse($this->model->isActive($quoteId));
    }

    /**
     * Test isPersistent with null quote ID
     */
    public function testIsPersistentWithNullQuoteId(): void
    {
        $this->assertFalse($this->model->isPersistent(null));
    }

    /**
     * Test isPersistent with persistent quote
     */
    public function testIsPersistentWithPersistentQuote(): void
    {
        $quoteId = 123;
        $tableName = 'quote';

        $this->resourceConnectionMock->expects($this->once())
            ->method('getTableName')
            ->with('quote')
            ->willReturn($tableName);

        $this->selectMock->expects($this->once())
            ->method('from')
            ->with($tableName, 'is_persistent')
            ->willReturnSelf();

        $this->selectMock->expects($this->once())
            ->method('where')
            ->with('entity_id = ?', $quoteId)
            ->willReturnSelf();

        $this->connectionMock->expects($this->once())
            ->method('fetchOne')
            ->with($this->selectMock)
            ->willReturn('1');

        $this->assertTrue($this->model->isPersistent($quoteId));
    }

    /**
     * Test isPersistent with non-persistent quote
     */
    public function testIsPersistentWithNonPersistentQuote(): void
    {
        $quoteId = 123;
        $tableName = 'quote';

        $this->resourceConnectionMock->expects($this->once())
            ->method('getTableName')
            ->with('quote')
            ->willReturn($tableName);

        $this->selectMock->expects($this->once())
            ->method('from')
            ->with($tableName, 'is_persistent')
            ->willReturnSelf();

        $this->selectMock->expects($this->once())
            ->method('where')
            ->with('entity_id = ?', $quoteId)
            ->willReturnSelf();

        $this->connectionMock->expects($this->once())
            ->method('fetchOne')
            ->with($this->selectMock)
            ->willReturn('0');

        $this->assertFalse($this->model->isPersistent($quoteId));
    }

    /**
     * Test type casting from database on isPersistent
     */
    public function testIsPersistentTypeCasting(): void
    {
        $quoteId = 123;
        $tableName = 'quote';

        $this->resourceConnectionMock->expects($this->once())
            ->method('getTableName')
            ->willReturn($tableName);

        $this->selectMock->expects($this->once())
            ->method('from')
            ->willReturnSelf();

        $this->selectMock->expects($this->once())
            ->method('where')
            ->willReturnSelf();

        $this->connectionMock->expects($this->once())
            ->method('fetchOne')
            ->willReturn('');

        $this->assertFalse($this->model->isPersistent($quoteId));
    }
}
