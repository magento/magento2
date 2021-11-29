<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Adapter;

use Magento\AdvancedSearch\Model\Client\ClientInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Setup\CategorySetup;
use Magento\CatalogSearch\Model\Indexer\IndexerHandlerFactory;
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;
use Magento\Elasticsearch\Model\Adapter\Index\BuilderInterface;
use Magento\Elasticsearch\Model\Adapter\Index\IndexNameResolver;
use Magento\Elasticsearch\Model\Indexer\IndexerHandler;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Framework\Indexer\DimensionFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Indexer\Model\Indexer;
use Magento\Store\Model\StoreDimensionProvider;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Elasticsearch adapter model test class
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ElasticsearchTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var IndexNameResolver
     */
    private $indexNameResolver;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Elasticsearch
     */
    private $adapter;

    /**
     * @var BuilderInterface
     */
    private $indexBuilder;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var string
     */
    private $newIndex;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->indexNameResolver = $this->objectManager->get(IndexNameResolver::class);
        $this->adapter = $this->objectManager->get(Elasticsearch::class);
        $this->storeManager = $this->objectManager->create(StoreManagerInterface::class);
        $connectionManager = $this->objectManager->create(ConnectionManager::class);
        $this->client = $connectionManager->getConnection();
        $this->indexBuilder = $this->objectManager->get(BuilderInterface::class);
        $this->arrayManager = $this->objectManager->get(ArrayManager::class);
    }

    /**
     * @inheritdoc
     */
    public function tearDown(): void
    {
        if ($this->newIndex) {
            $this->deleteIndex($this->newIndex);
        }
    }

    /**
     * Tests possibility to create mapping if adapter has obsolete index name in cache
     *
     * @magentoDataFixture Magento/Elasticsearch/_files/select_attribute.php
     * @return void
     */
    public function testRetryOnIndexNotFoundException(): void
    {
        $this->reindex();
        $this->updateElasticsearchIndex();
        $this->createNewAttribute();
        $mapping = $this->client->getMapping(['index' => $this->newIndex]);
        $pathField = $this->arrayManager->findPath('properties', $mapping);
        $attributes = $this->arrayManager->get($pathField, $mapping, []);
        $this->assertArrayHasKey('multiselect_attribute', $attributes);
    }

    /**
     * Test that new fields are not added during document indexing that were not explicitly defined in the mapping
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @magentoDbIsolation disabled
     */
    public function testMappingShouldNotChangeAfterReindex(): void
    {
        $index = 'catalogsearch_fulltext';
        $storeId = (int) $this->storeManager->getStore('fixture_second_store')->getId();
        // Create empty index and save the initial mapping
        $dimensionFactory = $this->objectManager->get(DimensionFactory::class);
        $dimensions = [
            StoreDimensionProvider::DIMENSION_NAME => $dimensionFactory->create(
                StoreDimensionProvider::DIMENSION_NAME,
                (string) $storeId
            )
        ];
        $indexHandlerFactory = $this->objectManager->get(IndexerHandlerFactory::class);
        $indexHandler = $indexHandlerFactory->create(
            [
                'data' => [
                    'indexer_id' => $index
                ]
            ]
        );
        $indexHandler->cleanIndex($dimensions);
        $indexHandler->saveIndex($dimensions, new \ArrayIterator([]));
        $propertiesBefore = $this->getIndexMapping($storeId);
        $this->reindex();
        $propertiesAfter = $this->getIndexMapping($storeId);
        $this->assertEquals($propertiesBefore, $propertiesAfter);
    }

    /**
     * Prepare and save new attribute
     *
     * @return void
     */
    public function createNewAttribute(): void
    {
        /** @var CategorySetup $installer */
        $installer = $this->objectManager->get(CategorySetup::class);
        /** @var Attribute $attribute */
        $multiselectAttribute = $this->objectManager->get(Attribute::class);
        $multiselectAttribute->setData(
            [
                'attribute_code' => 'multiselect_attribute',
                'entity_type_id' => $installer->getEntityTypeId('catalog_product'),
                'is_global' => 1,
                'is_user_defined' => 1,
                'frontend_input' => 'multiselect',
                'is_unique' => 0,
                'is_required' => 0,
                'is_searchable' => 1,
                'is_visible_in_advanced_search' => 0,
                'is_comparable' => 0,
                'is_filterable' => 1,
                'is_filterable_in_search' => 0,
                'is_used_for_promo_rules' => 0,
                'is_html_allowed_on_front' => 1,
                'is_visible_on_front' => 0,
                'used_in_product_listing' => 0,
                'used_for_sort_by' => 0,
                'frontend_label' => ['Multiselect Attribute'],
                'backend_type' => 'text',
                'backend_model' => ArrayBackend::class,
                'option' => [
                    'value' => [
                        'dog' => ['Dog'],
                        'cat' => ['Cat'],
                    ],
                    'order' => [
                        'dog' => 1,
                        'cat' => 2,
                    ],
                ],
            ]
        );
        $multiselectAttribute->save();
    }

    /**
     * Prepare new index and delete old. Keep cache alive.
     *
     * @return void
     */
    private function updateElasticsearchIndex(): void
    {
        $storeId = (int)$this->storeManager->getDefaultStoreView()->getId();
        $mappedIndexerId = 'product';
        $this->adapter->updateIndexMapping($storeId, $mappedIndexerId, 'select_attribute');
        $oldIndex = $this->indexNameResolver->getIndexFromAlias($storeId, $mappedIndexerId);
        $this->newIndex = $oldIndex . '1';
        $this->deleteIndex($this->newIndex);
        $this->indexBuilder->setStoreId($storeId);
        $this->client->createIndex($this->newIndex, ['settings' => $this->indexBuilder->build()]);
        $this->client->updateAlias(
            $this->indexNameResolver->getIndexNameForAlias($storeId, $mappedIndexerId),
            $this->newIndex,
            $oldIndex
        );
        $this->client->deleteIndex($oldIndex);
    }

    /**
     * Delete index by name if exists
     *
     * @param $newIndex
     */
    private function deleteIndex($newIndex): void
    {
        if ($this->client->indexExists($newIndex)) {
            $this->client->deleteIndex($newIndex);
        }
    }

    /**
     * @return void
     */
    private function reindex(): void
    {
        $indexer = $this->objectManager->create(Indexer::class);
        $indexer->load('catalogsearch_fulltext');
        $indexer->reindexAll();
    }

    /**
     * @param int $storeId
     * @return array
     */
    private function getIndexMapping(int $storeId): array
    {
        $indexName = $this->indexNameResolver->getIndexName($storeId, 'product', []);
        $mapping = $this->client->getMapping(['index' => $indexName]);
        $pathField = $this->arrayManager->findPath('properties', $mapping);
        return $this->arrayManager->get($pathField, $mapping, []);
    }
}
