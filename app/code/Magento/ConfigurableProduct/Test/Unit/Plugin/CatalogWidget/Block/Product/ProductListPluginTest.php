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
     * The products matching the widget condition reach the configurable lookup as a subquery, not as an id list
     *
     * @return void
     * @throws LocalizedException
     */
    public function testAfterCreateCollectionLooksUpConfigurablesBySubquery(): void
    {
        $storeId = 1;
        $visibilityAttributeId = 1;
        $linkField = 'entity_id';
        $visibleCatalogIds = [Visibility::VISIBILITY_IN_CATALOG, Visibility::VISIBILITY_BOTH];
        $configurableEntityIds = [3];

        $baseIdsSelect = $this->createMock(Select::class);
        $baseIdsSelect->expects($this->once())->method('resetJoinLeft')->willReturnSelf();
        $baseCollection = $this->createMock(Collection::class);
        $baseCollection->expects($this->once())->method('getAllIdsSql')->willReturn($baseIdsSelect);
        $baseCollection->expects($this->never())->method('getAllIds');
        $subject = $this->createMock(ProductsList::class);
        $subject->expects($this->once())->method('getBaseCollection')->willReturn($baseCollection);

        $resultSelect = $this->createMock(Select::class);
        $resultSelect->expects($this->once())
            ->method('orWhere')
            ->with('e.entity_id IN (?)', $configurableEntityIds)
            ->willReturnSelf();
        $result = $this->createMock(Collection::class);
        $result->expects($this->never())->method('getAllIds');
        $result->expects($this->once())->method('getSelect')->willReturn($resultSelect);
        $entity = $this->createMock(EntityMetadataInterface::class);
        $entity->expects($this->once())->method('getLinkField')->willReturn($linkField);
        $this->metadataPool->expects($this->once())
            ->method('getMetadata')
            ->with(ProductInterface::class)
            ->willReturn($entity);

        $visibilityAttribute = $this->createMock(AbstractAttribute::class);
        $visibilityAttribute->expects($this->once())->method('getId')->willReturn($visibilityAttributeId);
        $this->config->expects($this->once())->method('getAttribute')->with(Product::ENTITY, 'visibility')
            ->willReturn($visibilityAttribute);
        $store = $this->createMock(Store::class);
        $store->expects($this->once())->method('getId')->willReturn($storeId);
        $this->storeManager->expects($this->once())->method('getStore')->willReturn($store);

        $select = $this->createMock(Select::class);
        $select->expects($this->once())
            ->method('from')
            ->with(['e' => 'catalog_product_entity'], [])
            ->willReturn($select);
        $select->expects($this->exactly(3))
            ->method('joinInner')
            ->willReturn($select);
        $select->expects($this->once())
            ->method('joinLeft')
            ->willReturnSelf();
        $whereConditions = [];
        $select->expects($this->exactly(2))
            ->method('where')
            ->willReturnCallback(function ($condition, $value) use (&$whereConditions, $select) {
                $whereConditions[] = [$condition, $value];
                return $select;
            });
        $connection = $this->createMock(AdapterInterface::class);
        $connection->expects($this->once())->method('select')->willReturn($select);
        $connection->expects($this->never())->method('fetchCol');
        $this->resource->expects($this->once())->method('getConnection')->willReturn($connection);
        $this->resource->expects($this->exactly(5))
            ->method('getTableName')
            ->willReturnCallback(fn($param) => match ([$param]) {
                ['catalog_product_entity'] => 'catalog_product_entity',
                ['catalog_product_super_link'] => 'catalog_product_super_link',
                default => $param
            });

        $collection = $this->createMock(Collection::class);
        $this->productCollectionFactory->expects($this->once())->method('create')->willReturn($collection);
        $this->catalogProductVisibility->expects($this->once())
            ->method('getVisibleInCatalogIds')
            ->willReturn($visibleCatalogIds);
        $collection->expects($this->once())->method('setVisibility')->with($visibleCatalogIds);
        $collection->expects($this->once())->method('addFieldToFilter')->with('entity_id', ['in' => $select]);
        $collection->expects($this->once())->method('getAllIds')->willReturn($configurableEntityIds);

        $this->assertSame($result, $this->plugin->afterCreateCollection($subject, $result));
        $this->assertSame(['link_table.product_id IN (?)', $baseIdsSelect], $whereConditions[0]);
    }
}
