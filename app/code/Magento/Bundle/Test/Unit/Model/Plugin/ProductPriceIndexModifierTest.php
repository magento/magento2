<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model\Plugin;

use Magento\Bundle\Model\Plugin\ProductPriceIndexModifier;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\IndexTableStructure;
use Magento\CatalogInventory\Model\Indexer\ProductPriceIndexFilter;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductPriceIndexModifierTest extends TestCase
{
    private const CONNECTION_NAME = 'indexer';

    /**
     * @var ResourceConnection|MockObject
     */
    private ResourceConnection $resourceConnection;

    /**
     * @var ProductPriceIndexModifier
     */
    private ProductPriceIndexModifier $plugin;

    /**
     * @var IndexTableStructure|MockObject
     */
    private IndexTableStructure $table;

    /**
     * @var ProductPriceIndexFilter|MockObject
     */
    private ProductPriceIndexFilter $subject;

    protected function setUp(): void
    {
        $this->table = $this->createMock(IndexTableStructure::class);
        $this->subject = $this->createMock(ProductPriceIndexFilter::class);
        $this->resourceConnection = $this->createMock(ResourceConnection::class);
        $this->plugin = new ProductPriceIndexModifier($this->resourceConnection, self::CONNECTION_NAME);
    }

    public function testAroundModifyPriceNoEntities(): void
    {
        $called = false;
        $callable = function () use (&$called) {
            $called = true;
        };

        $this->plugin->aroundModifyPrice($this->subject, $callable, $this->table);
        $this->assertTrue($called);
    }

    public function testAroundModifyPriceFilteredEntities()
    {
        $priceTableName = 'catalog_product_index_price_temp';
        $entities = [1, 2];
        $this->table->expects($this->exactly(2))
            ->method('getTableName')
            ->willReturn($priceTableName);
        $select = $this->createMock(Select::class);
        $select->expects($this->exactly(2))
            ->method('from')
            ->with(['selection' => 'catalog_product_bundle_selection'], 'selection_id');
        $select->expects($this->exactly(2))
            ->method('joinInner')
            ->with(['price' => $priceTableName],
                implode(' AND ', ['price.entity_id = selection.product_id']),
                null);
        $select->expects($this->exactly(4))
            ->method('where')
            ->withConsecutive(
                ['selection.product_id = ?', $entities[0]],
                ['price.tax_class_id = ?', \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC],
                ['selection.product_id = ?', $entities[1]],
                ['price.tax_class_id = ?', \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC]
            );
        $connection = $this->createMock(AdapterInterface::class);
        $connection->expects($this->exactly(2))
            ->method('select')
            ->willReturn($select);
        $connection->expects($this->exactly(2))
            ->method('fetchOne')
            ->with($select)
            ->willReturn(null);
        $this->resourceConnection->expects($this->exactly(2))
            ->method('getConnection')
            ->with(self::CONNECTION_NAME)
            ->willReturn($connection);

        $calledPriceTable = '';
        $calledEntities = [];
        $callable = function () use (&$calledPriceTable, &$calledEntities, $priceTableName, $entities) {
            $calledPriceTable = $priceTableName;
            $calledEntities = $entities;
        };
        $this->plugin->aroundModifyPrice($this->subject, $callable, $this->table, [1, 2]);
        $this->assertSame($calledPriceTable, $priceTableName);
        $this->assertSame($calledEntities, $entities);
    }
}
