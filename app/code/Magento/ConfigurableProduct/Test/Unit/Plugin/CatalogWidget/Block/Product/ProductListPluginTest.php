<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Plugin\CatalogWidget\Block\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogWidget\Block\Product\ProductsList;
use Magento\ConfigurableProduct\Plugin\CatalogWidget\Block\Product\ProductsListPlugin;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\DB\Select;
use Magento\Framework\DataObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductListPluginTest extends TestCase
{
    /**
     * @var CollectionFactory|MockObject
     */
    protected CollectionFactory $productCollectionFactory;

    /**
     * @var Visibility|MockObject
     */
    protected Visibility $catalogProductVisibility;

    /**
     * @var ResourceConnection|MockObject
     */
    protected ResourceConnection $resource;

    /**
     * @var MetadataPool
     */
    protected MetadataPool $metadataPool;

    /**
     * @var ProductsListPlugin
     */
    protected ProductsListPlugin $plugin;

    protected function setUp(): void
    {
        $this->productCollectionFactory = $this->createMock(CollectionFactory::class);
        $this->catalogProductVisibility = $this->createMock(Visibility::class);
        $this->resource = $this->createMock(ResourceConnection::class);
        $this->metadataPool = $this->createMock(MetadataPool::class);

        $this->plugin = new ProductsListPlugin(
            $this->productCollectionFactory,
            $this->catalogProductVisibility,
            $this->resource,
            $this->metadataPool
        );

        parent::setUp();
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testAfterCreateCollectionNoCount(): void
    {
        $subject = $this->createMock(ProductsList::class);
        $baseCollection = $this->createMock(Collection::class);
        $baseCollection->expects($this->once())->method('getAllIds')->willReturn([]);
        $subject->expects($this->once())->method('getBaseCollection')->willReturn($baseCollection);
        $result = $this->createMock(Collection::class);
        $result->expects($this->once())->method('getAllIds')->willReturn([]);

        $this->assertSame($result, $this->plugin->afterCreateCollection($subject, $result));
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testAfterCreateCollectionSuccess(): void
    {
        $linkField = 'entity_id';
        $baseCollection = $this->createMock(Collection::class);
        $baseCollection->expects($this->once())->method('getAllIds')->willReturn([2]);
        $subject = $this->createMock(ProductsList::class);
        $subject->expects($this->once())->method('getBaseCollection')->willReturn($baseCollection);

        $result = $this->createMock(Collection::class);
        $result->expects($this->once())->method('getAllIds')->willReturn([1]);
        $result->expects($this->once())->method('addItem');
        $entity = $this->createMock(EntityMetadataInterface::class);
        $entity->expects($this->once())->method('getLinkField')->willReturn($linkField);
        $this->metadataPool->expects($this->once())
            ->method('getMetadata')
            ->with(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->willReturn($entity);

        $select = $this->createMock(Select::class);
        $select->expects($this->once())
            ->method('from')
            ->with(['e' => 'catalog_product_entity'], ['entity_table.entity_id'])
            ->willReturn($select);
        $select->expects($this->exactly(2))
            ->method('joinInner')
            ->willReturn($select);
        $select->expects($this->once())->method('where')->with('link_table.product_id IN (?)', [1, 2]);
        $connection = $this->createMock(AdapterInterface::class);
        $connection->expects($this->once())->method('select')->willReturn($select);
        $connection->expects($this->once())->method('fetchCol')->willReturn([2]);
        $this->resource->expects($this->once())->method('getConnection')->willReturn($connection);
        $this->resource->expects($this->exactly(3))
            ->method('getTableName')
            ->willReturnCallback(fn($param) => match ([$param]) {
                ['catalog_product_entity'] => 'catalog_product_entity',
                ['catalog_product_super_link'] => 'catalog_product_super_link'
            });

        $collection = $this->createMock(Collection::class);
        $this->productCollectionFactory->expects($this->once())->method('create')->willReturn($collection);
        $this->catalogProductVisibility->expects($this->once())->method('getVisibleInCatalogIds');
        $collection->expects($this->once())->method('setVisibility');
        $collection->expects($this->once())->method('addIdFilter');
        $product = $this->createMock(Product::class);
        $product->expects($this->once())->method('load')->willReturn($product);
        $collection->expects($this->once())->method('getItems')->willReturn([$product]);

        $this->plugin->afterCreateCollection($subject, $result);
    }
}
