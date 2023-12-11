<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Eav\Action;

use Magento\Catalog\Model\Indexer\Product\Eav\Action\Row;
use Magento\Catalog\Model\Indexer\Product\Eav\Action\Rows;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\Decimal;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\DecimalFactory;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\Source;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\SourceFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\InputException;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RowsTest extends TestCase
{
    /**
     * @var DecimalFactory|MockObject
     */
    private $eavDecimalFactoryMock;

    /**
     * @var SourceFactory|MockObject
     */
    private $eavSourceFactoryMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var Rows
     */
    private $model;

    protected function setUp(): void
    {
        $this->eavDecimalFactoryMock = $this->createMock(DecimalFactory::class);
        $this->eavSourceFactoryMock = $this->createMock(SourceFactory::class);
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->model = new Rows(
            $this->eavDecimalFactoryMock,
            $this->eavSourceFactoryMock,
            $this->scopeConfigMock
        );
    }

    public function testEmptyIds()
    {
        $this->expectException(InputException::class);
        $this->expectExceptionMessage('Bad value was supplied.');
        $this->model->execute(null);
    }

    public function testExecute(): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Row::ENABLE_EAV_INDEXER, ScopeInterface::SCOPE_STORE)
            ->willReturn(true);

        $eavDecimalMock = $this->createMock(Decimal::class);
        $this->eavDecimalFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($eavDecimalMock);
        $eavSourceMock = $this->createMock(Source::class);
        $this->eavSourceFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($eavSourceMock);

        foreach ([$eavDecimalMock, $eavSourceMock] as $indexerMock) {
            $indexerMock->expects($this->atLeastOnce())
                ->method('getRelationsByChild')
                ->with([2, 4, 5])
                ->willReturn([]);
            $indexerMock->expects($this->atLeastOnce())
                ->method('getRelationsByParent')
                ->with([2, 4, 5])
                ->willReturn([]);
            $indexerMock->expects($this->once())
                ->method('reindexEntities')
                ->with([2, 4, 5])
                ->willReturnSelf();
            $mainTable = 'main_table_name';
            $indexerMock->expects($this->atLeastOnce())
                ->method('getMainTable')
                ->willReturn($mainTable);

            $connectionMock = $this->createMock(AdapterInterface::class);
            $indexerMock->expects($this->atLeastOnce())
                ->method('getConnection')
                ->willReturn($connectionMock);
            $connectionMock->expects($this->once())
                ->method('beginTransaction')
                ->willReturnSelf();
            $connectionMock->expects($this->once())
                ->method('quoteInto')
                ->with('entity_id IN (?)', [2, 4, 5], 'INT')
                ->willReturn('entity_id IN (2, 4, 5)');
            $connectionMock->expects($this->once())
                ->method('delete')
                ->with($mainTable, 'entity_id IN (2, 4, 5)')
                ->willReturn(3);
            $idxTable = 'idx_table_name';
            $indexerMock->expects($this->atLeastOnce())
                ->method('getIdxTable')
                ->with()
                ->willReturn($idxTable);
            $indexerMock->expects($this->once())
                ->method('insertFromTable')
                ->with($idxTable, $mainTable)
                ->willReturnSelf();
            $connectionMock->expects($this->once())
                ->method('commit')
                ->willReturnSelf();
        }

        $ids = [2, 4, 5];
        $this->model->execute($ids);
    }
}
