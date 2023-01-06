<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model\Plugin;

use Magento\Bundle\Model\Plugin\ProductPriceIndexModifier;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\IndexTableStructure;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\Indexer\ProductPriceIndexFilter;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductPriceIndexModifierTest extends TestCase
{
    private const CONNECTION_NAME = 'indexer';

    /**
     * @var StockConfigurationInterface|StockConfigurationInterface&MockObject|MockObject
     */
    private StockConfigurationInterface $stockConfiguration;

    /**
     * @var ResourceConnection|MockObject
     */
    private ResourceConnection $resourceConnection;

    /**
     * @var MetadataPool|MetadataPool&MockObject|MockObject
     */
    private MetadataPool $metadataPool;

    /**
     * @var ProductPriceIndexModifier
     */
    private ProductPriceIndexModifier $plugin;

    /**
     * @var IndexTableStructure|MockObject
     */
    private IndexTableStructure $table;

    /**
     * @var ProductRepositoryInterface|ProductRepositoryInterface&MockObject|MockObject
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var ProductPriceIndexFilter|MockObject
     */
    private ProductPriceIndexFilter $subject;

    protected function setUp(): void
    {
        $this->stockConfiguration = $this->createMock(StockConfigurationInterface::class);
        $this->table = $this->createMock(IndexTableStructure::class);
        $this->subject = $this->createMock(ProductPriceIndexFilter::class);
        $this->resourceConnection = $this->createMock(ResourceConnection::class);
        $this->metadataPool = $this->createMock(MetadataPool::class);
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->plugin = new ProductPriceIndexModifier(
            $this->stockConfiguration,
            $this->resourceConnection,
            $this->metadataPool,
            $this->productRepository,
            self::CONNECTION_NAME
        );
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
        $link = $this->createMock(EntityMetadataInterface::class);
        $link->expects($this->once())->method('getLinkField')->willReturn('id');
        $this->metadataPool->expects($this->once())
            ->method('getMetadata')
            ->with(ProductInterface::class)
            ->willReturn($link);
        $select = $this->createMock(Select::class);
        $select->expects($this->once())
            ->method('from');
        $select->expects($this->exactly(2))
            ->method('joinInner');
        $select->expects($this->exactly(2))
            ->method('where');
        $connection = $this->createMock(AdapterInterface::class);
        $connection->expects($this->once())
            ->method('select')
            ->willReturn($select);
        $connection->expects($this->exactly(1))
            ->method('fetchAll')
            ->with($select)
            ->willReturn([
                [
                    'bundle_id' => 1,
                    'child_product_id' => 1
                ],
                [
                    'bundle_id' => 1,
                    'child_product_id' => 2
                ]
            ]);
        $this->resourceConnection->expects($this->once())
            ->method('getConnection')
            ->with(self::CONNECTION_NAME)
            ->willReturn($connection);

        $bundleProduct1 = $this->getMockBuilder(ProductInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['getPriceType'])
            ->getMockForAbstractClass();
        $bundleProduct1->expects($this->once())->method('getPriceType')
            ->willReturn(1);
        $bundleProduct2 = $this->getMockBuilder(ProductInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['getPriceType'])
            ->getMockForAbstractClass();
        $bundleProduct2->expects($this->once())->method('getPriceType')
            ->willReturn(1);

        $this->productRepository->expects($this->exactly(2))
            ->method('getById')
            ->willReturnOnConsecutiveCalls($bundleProduct1, $bundleProduct2);

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
