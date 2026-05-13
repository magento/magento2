<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\ResourceModel\Quote;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Sales\Model\ResourceModel\Quote\Delete;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Quote\Delete resource model.
 */
class DeleteTest extends TestCase
{
    private const QUOTE_TABLE          = 'quote';
    private const QUOTE_TABLE_PREFIXED = 'pref_quote';

    /**
     * @var ResourceConnection|MockObject
     */
    private ResourceConnection|MockObject $resourceConnection;

    /**
     * @var AdapterInterface|MockObject
     */
    private AdapterInterface|MockObject $connection;

    /**
     * @var Delete
     */
    private Delete $delete;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->resourceConnection = $this->createMock(ResourceConnection::class);
        $this->connection         = $this->createMock(AdapterInterface::class);

        $this->resourceConnection->method('getConnection')->willReturn($this->connection);
        $this->resourceConnection
            ->method('getTableName')
            ->with(self::QUOTE_TABLE)
            ->willReturn(self::QUOTE_TABLE_PREFIXED);

        $this->delete = new Delete($this->resourceConnection);
    }

    /**
     * Asserts that deleteByIds issues a single bulk DELETE on the quote table
     * using the prefixed table name and an IN condition.
     */
    public function testDeleteByIdsExecutesBulkDelete(): void
    {
        $ids = [1, 2, 3];

        $this->connection->expects($this->once())
            ->method('delete')
            ->with(
                self::QUOTE_TABLE_PREFIXED,
                ['entity_id IN (?)' => $ids]
            );

        $this->delete->deleteByIds($ids);
    }

    /**
     * Asserts that deleteByIds does nothing when the IDs array is empty,
     * avoiding a DELETE with no WHERE condition.
     */
    public function testDeleteByIdsDoesNothingForEmptyArray(): void
    {
        $this->connection->expects($this->never())->method('delete');

        $this->delete->deleteByIds([]);
    }
}
