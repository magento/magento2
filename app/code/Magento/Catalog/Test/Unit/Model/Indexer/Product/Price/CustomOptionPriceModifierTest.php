<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Price;

use Magento\Catalog\Helper\Data;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\CustomOptionPriceModifier;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\IndexTableStructure;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Sql\ColumnValueExpressionFactory;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Indexer\Table\StrategyInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomOptionPriceModifierTest extends TestCase
{
    /**
     * @var ResourceConnection|MockObject
     */
    private ResourceConnection $resource;

    /**
     * @var MetadataPool|MockObject
     */
    private MetadataPool $metadataPool;

    /**
     * @var ColumnValueExpressionFactory|MockObject
     */
    private ColumnValueExpressionFactory $columnValueExpressionFactory;

    /**
     * @var Data|MockObject
     */
    private Data $dataHelper;

    /**
     * @var StrategyInterface|MockObject
     */
    private StrategyInterface $tableStrategy;

    /**
     * @var CustomOptionPriceModifier
     */
    private CustomOptionPriceModifier $priceModifier;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->resource = $this->createMock(ResourceConnection::class);
        $this->metadataPool = $this->createMock(MetadataPool::class);
        $this->columnValueExpressionFactory = $this->createMock(ColumnValueExpressionFactory::class);
        $this->dataHelper = $this->createMock(Data::class);
        $this->tableStrategy = $this->createMock(StrategyInterface::class);
        $this->priceModifier = new CustomOptionPriceModifier(
            $this->resource,
            $this->metadataPool,
            $this->columnValueExpressionFactory,
            $this->dataHelper,
            $this->tableStrategy
        );

        parent::setUp();
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testModifyPrice(): void
    {
        $priceTable = $this->createMock(IndexTableStructure::class);
        $priceTable->expects($this->exactly(2))->method('getTableName')->willReturn('temporary_table_name');

        $select = $this->createMock(Select::class);
        $select->expects($this->any())->method('from')->willReturn($select);
        $select->expects($this->any())->method('join')->willReturn($select);
        $select->expects($this->any())->method('group')->willReturn($select);
        $select->expects($this->any())->method('columns')->willReturn($select);

        $connection = $this->createMock(AdapterInterface::class);
        $connection->expects($this->exactly(2))->method('delete');
        $connection->expects($this->any())->method('select')->willReturn($select);
        $connection->expects($this->any())->method('fetchRow')->willReturn(['exists']);
        $connection->expects($this->exactly(4))->method('query');
        $connection->expects($this->exactly(2))->method('dropTemporaryTable');
        $this->resource->expects($this->any())->method('getConnection')->willReturn($connection);
        $this->resource->expects($this->any())->method('getTableName')->willReturn('table');
        $this->tableStrategy->expects($this->any())
            ->method('getTableName')
            ->willReturn('table_name');

        $metadata = $this->createMock(EntityMetadataInterface::class);
        $this->metadataPool->expects($this->any())->method('getMetadata')->willReturn($metadata);
        $this->dataHelper->expects($this->once())->method('isPriceGlobal')->willReturn(true);

        $this->priceModifier->modifyPrice($priceTable);
    }
}
