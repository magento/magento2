<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model\ResourceModel\Indexer;

use Magento\Bundle\Model\ResourceModel\Indexer\Price;
use Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\BasePriceModifier;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\IndexTableStructureFactory;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Query\JoinAttributeProcessor;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Module\Manager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class to test Bundle products Price indexer resource model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PriceTest extends TestCase
{
    /**
     * @var string
     */
    private $connectionName = 'test_connection';

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var Price
     */
    private $priceModel;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->connectionMock = $this->createMock(AdapterInterface::class);
        $this->resourceMock = $this->createMock(ResourceConnection::class);
        $this->resourceMock->method('getConnection')
            ->with($this->connectionName)
            ->willReturn($this->connectionMock);
        $this->resourceMock->method('getTableName')->willReturnArgument(0);

        /** @var IndexTableStructureFactory|MockObject $indexTableStructureFactory */
        $indexTableStructureFactory = $this->createMock(IndexTableStructureFactory::class);
        /** @var TableMaintainer|MockObject $tableMaintainer */
        $tableMaintainer = $this->createMock(TableMaintainer::class);
        /** @var MetadataPool|MockObject $metadataPool */
        $this->metadataPool = $this->createMock(MetadataPool::class);
        /** @var BasePriceModifier|MockObject $basePriceModifier */
        $basePriceModifier = $this->createMock(BasePriceModifier::class);
        /** @var JoinAttributeProcessor|MockObject $joinAttributeProcessor */
        $joinAttributeProcessor = $this->createMock(JoinAttributeProcessor::class);
        /** @var ManagerInterface|MockObject $eventManager */
        $eventManager = $this->createMock(ManagerInterface::class);
        /** @var Manager|MockObject $moduleManager */
        $moduleManager = $this->createMock(Manager::class);
        $fullReindexAction = false;

        $this->priceModel = new Price(
            $indexTableStructureFactory,
            $tableMaintainer,
            $this->metadataPool,
            $this->resourceMock,
            $basePriceModifier,
            $joinAttributeProcessor,
            $eventManager,
            $moduleManager,
            $fullReindexAction,
            $this->connectionName
        );
    }

    /**
     * @throws \ReflectionException
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function testCalculateDynamicBundleSelectionPrice(): void
    {
        $entity = 'entity_id';
        $price = 'idx.min_price * bs.selection_qty';
        //@codingStandardsIgnoreStart
        $selectQuery = "SELECT `i`.`entity_id`,
       `i`.`customer_group_id`,
       `i`.`website_id`,
       `bo`.`option_id`,
       `bs`.`selection_id`,
       IF(bo.type = 'select' OR bo.type = 'radio', 0, 1) AS `group_type`,
       `bo`.`required`                                   AS `is_required`,
       LEAST(IF(i.special_price > 0 AND i.special_price < 100,
            ROUND(idx.min_price * bs.selection_qty * (i.special_price / 100), 4), idx.min_price * bs.selection_qty),
            IFNULL((IF(i.tier_percent IS NOT NULL,
            ROUND((1 - i.tier_percent / 100) * idx.min_price * bs.selection_qty, 4), NULL)), idx.min_price *
                                                                                    bs.selection_qty)) AS `price`,
       IF(i.tier_percent IS NOT NULL, ROUND((1 - i.tier_percent / 100) * idx.min_price * bs.selection_qty, 4),
          NULL)  AS `tier_price`
        FROM `catalog_product_index_price_bundle_temp` AS `i`
         INNER JOIN `catalog_product_entity` AS `parent_product` ON parent_product.entity_id = i.entity_id AND
                                                (parent_product.created_in <= 1 AND parent_product.updated_in > 1)
         INNER JOIN `catalog_product_bundle_option` AS `bo` ON bo.parent_id = parent_product.row_id
         INNER JOIN `catalog_product_bundle_selection` AS `bs` ON bs.option_id = bo.option_id
         INNER JOIN `catalog_product_index_price_replica` AS `idx`
                    ON bs.product_id = idx.entity_id AND i.customer_group_id = idx.customer_group_id AND
                       i.website_id = idx.website_id
         INNER JOIN `cataloginventory_stock_status` AS `si` ON si.product_id = bs.product_id
        WHERE (i.price_type = 0)
            AND (si.stock_status = 1)
        ON DUPLICATE KEY UPDATE `entity_id`         = VALUES(`entity_id`),
                        `customer_group_id` = VALUES(`customer_group_id`),
                        `website_id`        = VALUES(`website_id`),
                        `option_id`         = VALUES(`option_id`),
                        `selection_id`      = VALUES(`selection_id`),
                        `group_type`        = VALUES(`group_type`),
                        `is_required`       = VALUES(`is_required`),
                        `price`             = VALUES(`price`),
                        `tier_price`        = VALUES(`tier_price`)";
        $processedQuery = "INSERT INTO `catalog_product_index_price_bundle_sel_temp` (,,,,,,,,) SELECT `i`.`entity_id`,
       `i`.`customer_group_id`,
       `i`.`website_id`,
       `bo`.`option_id`,
       `bs`.`selection_id`,
       IF(bo.type = 'select' OR bo.type = 'radio', 0, 1) AS `group_type`,
       `bo`.`required`                                   AS `is_required`,
       LEAST(IF(i.special_price > 0 AND i.special_price < 100,
            ROUND(idx.min_price * bs.selection_qty * (i.special_price / 100), 4), idx.min_price * bs.selection_qty),
            IFNULL((IF(i.tier_percent IS NOT NULL,
            ROUND((1 - i.tier_percent / 100) * idx.min_price * bs.selection_qty, 4), NULL)), idx.min_price *
                                                                                    bs.selection_qty)) AS `price`,
       IF(i.tier_percent IS NOT NULL, ROUND((1 - i.tier_percent / 100) * idx.min_price * bs.selection_qty, 4),
          NULL)  AS `tier_price`
        FROM `catalog_product_index_price_bundle_temp` AS `i`
         INNER JOIN `catalog_product_entity` AS `parent_product` ON parent_product.entity_id = i.entity_id AND
                                                (parent_product.created_in <= 1 AND parent_product.updated_in > 1)
         INNER JOIN `catalog_product_bundle_option` AS `bo` ON bo.parent_id = parent_product.row_id
         INNER JOIN `catalog_product_bundle_selection` AS `bs` ON bs.option_id = bo.option_id
         INNER JOIN `catalog_product_index_price_replica` AS `idx` USE INDEX (PRIMARY)
                    ON bs.product_id = idx.entity_id AND i.customer_group_id = idx.customer_group_id AND
                       i.website_id = idx.website_id
         INNER JOIN `cataloginventory_stock_status` AS `si` ON si.product_id = bs.product_id
        WHERE (i.price_type = 0)
            AND (si.stock_status = 1)
        ON DUPLICATE KEY UPDATE `entity_id`         = VALUES(`entity_id`),
                        `customer_group_id` = VALUES(`customer_group_id`),
                        `website_id`        = VALUES(`website_id`),
                        `option_id`         = VALUES(`option_id`),
                        `selection_id`      = VALUES(`selection_id`),
                        `group_type`        = VALUES(`group_type`),
                        `is_required`       = VALUES(`is_required`),
                        `price`             = VALUES(`price`),
                        `tier_price`        = VALUES(`tier_price`) ON DUPLICATE KEY UPDATE  = VALUES(), = VALUES(), = VALUES(), = VALUES(), = VALUES(), = VALUES(), = VALUES(), = VALUES(), = VALUES()";
        //@codingStandardsIgnoreEnd
        $this->connectionMock->expects($this->exactly(3))
            ->method('getCheckSql')
        ->willReturnCallback(function ($arg1, $arg2, $arg3) use ($price) {
            static $callCount = 0;
            $callCount++;
            switch ($callCount) {
                case 1:
                    if ($arg1 === 'i.special_price > 0 AND i.special_price < 100' &&
                        $arg2 === 'ROUND(' . $price . ' * (i.special_price / 100), 4)' &&
                        $arg3 === $price) {
                        return null;
                    }
                    break;
                case 2:
                    if ($arg1 === 'i.tier_percent IS NOT NULL' &&
                        $arg2 === 'ROUND((1 - i.tier_percent / 100) * ' . $price . ', 4)' &&
                        $arg3 === 'NULL') {
                        return null;
                    }
                    break;
                case 3:
                    if ($arg1 === "bo.type = 'select' OR bo.type = 'radio'" &&
                        $arg2 === '0' &&
                        $arg3 === '1') {
                        return null;
                    }
                    break;
            }
        });

        $select = $this->createMock(\Magento\Framework\DB\Select::class);
        $select->expects($this->once())->method('from')->willReturn($select);
        $select->expects($this->exactly(5))->method('join')->willReturn($select);
        $select->expects($this->exactly(2))->method('where')->willReturn($select);
        $select->expects($this->once())->method('columns')->willReturn($select);
        $select->expects($this->any())->method('__toString')->willReturn($selectQuery);

        $this->connectionMock->expects($this->once())->method('getIfNullSql');
        $this->connectionMock->expects($this->once())->method('getLeastSql');
        $this->connectionMock->expects($this->any())
            ->method('select')
            ->willReturn($select);
        $this->connectionMock->expects($this->exactly(9))->method('quoteIdentifier');
        $this->connectionMock->expects($this->once())->method('query')->with($processedQuery);

        $pool = $this->createMock(EntityMetadataInterface::class);
        $pool->expects($this->once())->method('getLinkField')->willReturn($entity);
        $this->metadataPool->expects($this->once())
            ->method('getMetadata')
            ->willReturn($pool);

        $this->invokeMethodViaReflection('calculateDynamicBundleSelectionPrice', []);
    }

    /**
     * Tests create Bundle Price temporary table
     */
    public function testGetBundlePriceTable(): void
    {
        $expectedTmpTableName = 'catalog_product_index_price_bundle_temp';
        $expectedTableName = 'catalog_product_index_price_bundle_tmp';

        $this->connectionMock->expects($this->once())
            ->method('createTemporaryTableLike')
            ->with($expectedTmpTableName, $expectedTableName, true);

        $this->assertEquals(
            $expectedTmpTableName,
            $this->invokeMethodViaReflection('getBundlePriceTable')
        );
    }

    /**
     * Tests create Bundle Selection Prices Index temporary table
     */
    public function testGetBundleSelectionTable(): void
    {
        $expectedTmpTableName = 'catalog_product_index_price_bundle_sel_temp';
        $expectedTableName = 'catalog_product_index_price_bundle_sel_tmp';

        $this->connectionMock->expects($this->once())
            ->method('createTemporaryTableLike')
            ->with($expectedTmpTableName, $expectedTableName, true);

        $this->assertEquals(
            $expectedTmpTableName,
            $this->invokeMethodViaReflection('getBundleSelectionTable')
        );
    }

    /**
     * Tests create Bundle Option Prices Index temporary table
     */
    public function testGetBundleOptionTable(): void
    {
        $expectedTmpTableName = 'catalog_product_index_price_bundle_opt_temp';
        $expectedTableName = 'catalog_product_index_price_bundle_opt_tmp';

        $this->connectionMock->expects($this->once())
            ->method('createTemporaryTableLike')
            ->with($expectedTmpTableName, $expectedTableName, true);

        $this->assertEquals(
            $expectedTmpTableName,
            $this->invokeMethodViaReflection('getBundleOptionTable')
        );
    }

    /**
     * Invoke private method via reflection
     *
     * @param string $methodName
     * @param array $args
     * @return string
     * @throws \ReflectionException
     */
    private function invokeMethodViaReflection(string $methodName, array $args = []): string
    {
        $method = new \ReflectionMethod(
            Price::class,
            $methodName
        );
        $method->setAccessible(true);

        return (string)$method->invoke($this->priceModel, $args);
    }
}
