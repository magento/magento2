<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Plugin\CatalogWidget\Block\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Exception\LocalizedException;
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
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\DB\Select;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductListPluginTest extends TestCase
{
    /**
     * @var CollectionFactory|MockObject
     */
    private CollectionFactory $productCollectionFactory;

    /**
     * @var Visibility|MockObject
     */
    private Visibility $catalogProductVisibility;

    /**
     * @var ResourceConnection|MockObject
     */
    private ResourceConnection $resource;

    /**
     * @var MetadataPool|MockObject
     */
    private MetadataPool $metadataPool;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var Config|MockObject
     */
    private Config $config;

    /**
     * @var ProductsListPlugin
     */
    private ProductsListPlugin $plugin;

    protected function setUp(): void
    {
        $this->productCollectionFactory = $this->createMock(CollectionFactory::class);
        $this->catalogProductVisibility = $this->createMock(Visibility::class);
        $this->resource = $this->createMock(ResourceConnection::class);
        $this->metadataPool = $this->createMock(MetadataPool::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->config = $this->createMock(Config::class);

        $this->plugin = new ProductsListPlugin(
            $this->productCollectionFactory,
            $this->catalogProductVisibility,
            $this->resource,
            $this->metadataPool,
            $this->storeManager,
            $this->config
        );

        parent::setUp();
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function testAfterCreateCollectionNoCount(): void
    {
        $searchProductsSelect = $this->createMock(Select::class);
        $subject = $this->mockSubject($searchProductsSelect);
        $result = $this->createMock(Collection::class);
        $result->expects($this->never())->method('getAllIds');
        $result->expects($this->never())->method('getSelect');
        $this->mockParentSelect($searchProductsSelect, []);
        $this->productCollectionFactory->expects($this->never())->method('create');
        $this->catalogProductVisibility->expects($this->never())->method('getVisibleInCatalogIds');

        $this->assertSame($result, $this->plugin->afterCreateCollection($subject, $result));
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function testAfterCreateCollectionSuccess(): void
    {
        $productIds = [2];
        $visibleCatalogIds = [Visibility::VISIBILITY_IN_CATALOG, Visibility::VISIBILITY_BOTH];
        $configurableEntityIds = [3];

        $searchProductsSelect = $this->createMock(Select::class);
        $subject = $this->mockSubject($searchProductsSelect);

        $resultSelect = $this->createMock(Select::class);
        $resultSelect->expects($this->once())
            ->method('orWhere')
            ->with('e.entity_id IN (?)', $configurableEntityIds)
            ->willReturnSelf();
        $result = $this->createMock(Collection::class);
        $result->expects($this->never())->method('getAllIds');
        $result->expects($this->once())->method('getSelect')->willReturn($resultSelect);
        $this->mockParentSelect($searchProductsSelect, $productIds);

        $collection = $this->createMock(Collection::class);
        $this->productCollectionFactory->expects($this->once())->method('create')->willReturn($collection);
        $this->catalogProductVisibility->expects($this->once())
            ->method('getVisibleInCatalogIds')
            ->willReturn($visibleCatalogIds);
        $collection->expects($this->once())->method('setVisibility')->with($visibleCatalogIds);
        $collection->expects($this->once())->method('addIdFilter')->with($productIds);
        $collection->expects($this->once())->method('getAllIds')->willReturn($configurableEntityIds);

        $this->assertSame($result, $this->plugin->afterCreateCollection($subject, $result));
    }

    /**
     * Widget subject whose base collection exposes its id select instead of loading the ids
     *
     * @param Select $searchProductsSelect
     * @return ProductsList
     */
    private function mockSubject(Select $searchProductsSelect): ProductsList
    {
        $searchProductsSelect->expects($this->once())->method('resetJoinLeft')->willReturnSelf();
        $baseCollection = $this->createMock(Collection::class);
        $baseCollection->expects($this->never())->method('getAllIds');
        $baseCollection->expects($this->once())->method('getAllIdsSql')->willReturn($searchProductsSelect);
        $subject = $this->createMock(ProductsList::class);
        $subject->expects($this->once())->method('getBaseCollection')->willReturn($baseCollection);

        return $subject;
    }

    /**
     * Parent lookup select, asserting the child ids are bound as a subquery
     *
     * @param Select $searchProductsSelect
     * @param array $parentIds
     * @return void
     */
    private function mockParentSelect(Select $searchProductsSelect, array $parentIds): void
    {
        $entity = $this->createMock(EntityMetadataInterface::class);
        $entity->expects($this->once())->method('getLinkField')->willReturn('entity_id');
        $this->metadataPool->expects($this->once())
            ->method('getMetadata')
            ->with(ProductInterface::class)
            ->willReturn($entity);
        $visibilityAttribute = $this->createMock(AbstractAttribute::class);
        $visibilityAttribute->expects($this->once())->method('getId')->willReturn(1);
        $this->config->expects($this->once())->method('getAttribute')->with(Product::ENTITY, 'visibility')
            ->willReturn($visibilityAttribute);
        $store = $this->createMock(Store::class);
        $store->expects($this->once())->method('getId')->willReturn(1);
        $this->storeManager->expects($this->once())->method('getStore')->willReturn($store);

        $whereArguments = [];
        $select = $this->createMock(Select::class);
        $select->expects($this->once())
            ->method('from')
            ->with(['e' => 'catalog_product_entity'], [])
            ->willReturnSelf();
        $select->expects($this->exactly(3))->method('joinInner')->willReturnSelf();
        $select->expects($this->once())->method('joinLeft')->willReturnSelf();
        $select->expects($this->exactly(2))
            ->method('where')
            ->willReturnCallback(function (...$arguments) use ($select, &$whereArguments) {
                $whereArguments[] = $arguments;
                return $select;
            });
        $connection = $this->createMock(AdapterInterface::class);
        $connection->expects($this->once())->method('select')->willReturn($select);
        $connection->expects($this->once())
            ->method('fetchCol')
            ->with($select)
            ->willReturnCallback(function () use (&$whereArguments, $searchProductsSelect, $parentIds) {
                $this->assertSame(
                    ['link_table.product_id IN (?)', $searchProductsSelect],
                    array_slice($whereArguments[0], 0, 2)
                );
                return $parentIds;
            });
        $this->resource->expects($this->once())->method('getConnection')->willReturn($connection);
        $this->resource->expects($this->exactly(5))->method('getTableName')->willReturnArgument(0);
    }
}
