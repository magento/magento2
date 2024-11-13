<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Indexer\Product\Flat\Action;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Helper\Product\Flat\Indexer as IndexerHelper;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Indexer\Product\Flat\Processor;
use Magento\Catalog\Model\Indexer\Product\Flat\State;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Flat as FlatResource;
use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\Eav\Api\AttributeOptionManagementInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Indexer\TestCase;

/**
 * Full reindex Test
 */
class FullTest extends TestCase
{
    /** @var State */
    private $state;

    /** @var Processor */
    private $processor;

    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var FlatResource */
    private $flatResource;

    /** @var AttributeOptionManagementInterface */
    private $optionManagement;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var Full */
    private $action;

    /** @var IndexerHelper */
    private $productIndexerHelper;

    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass(): void
    {
        /*
         * Due to insufficient search engine isolation for Elasticsearch, this class must explicitly perform
         * a fulltext reindex prior to running its tests.
         *
         * This should be removed upon completing MC-19455.
         */
        $indexRegistry = Bootstrap::getObjectManager()->get(IndexerRegistry::class);
        $fulltextIndexer = $indexRegistry->get(Fulltext::INDEXER_ID);
        $fulltextIndexer->reindexAll();
    }

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->state = $this->objectManager->get(State::class);
        $this->processor = $this->objectManager->get(Processor::class);
        $this->flatResource = $this->objectManager->get(FlatResource::class);
        $this->optionManagement = $this->objectManager->get(AttributeOptionManagementInterface::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->action = $this->objectManager->get(Full::class);
        $this->productIndexerHelper = $this->objectManager->get(IndexerHelper::class);
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/frontend/flat_catalog_product 1
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     *
     * @return void
     */
    public function testReindexAll(): void
    {
        $this->assertTrue($this->state->isFlatEnabled());
        $this->processor->reindexAll();

        $categoryFactory = $this->objectManager->get(CategoryFactory::class);
        $listProduct = $this->objectManager->get(ListProduct::class);

        $category = $categoryFactory->create()->load(2);
        $layer = $listProduct->getLayer();
        $layer->setCurrentCategory($category);
        $productCollection = $layer->getProductCollection();

        $this->assertCount(1, $productCollection);

        /** @var $product \Magento\Catalog\Model\Product */
        foreach ($productCollection as $product) {
            $this->assertEquals('Simple Product', $product->getName());
            $this->assertEquals('Short description', $product->getShortDescription());
        }
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple_multistore.php
     * @magentoConfigFixture current_store catalog/frontend/flat_catalog_product 1
     * @magentoConfigFixture fixturestore_store catalog/frontend/flat_catalog_product 1
     *
     * @return void
     */
    public function testReindexAllMultipleStores(): void
    {
        $this->assertTrue($this->state->isFlatEnabled());
        $this->processor->reindexAll();

        /** @var ProductCollectionFactory $productCollectionFactory */
        $productCollectionFactory = $this->objectManager->create(ProductCollectionFactory::class);
        /** @var StoreManagerInterface $storeManager */
        $storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $store = $storeManager->getStore('fixturestore');
        $currentStore = $storeManager->getStore();

        $expectedData = [
            $storeManager->getDefaultStoreView()->getId() => 'Simple Product One',
            $store->getId() => 'StoreTitle',
        ];

        try {
            foreach ($expectedData as $storeId => $productName) {
                $storeManager->setCurrentStore($storeId);
                $productCollection = $productCollectionFactory->create();

                $this->assertTrue(
                    $productCollection->isEnabledFlat(),
                    'Flat should be enabled for product collection.'
                );

                $productCollection->addIdFilter(1)->addAttributeToSelect(ProductInterface::NAME);

                $this->assertEquals(
                    $productName,
                    $productCollection->getFirstItem()->getName(),
                    'Wrong product name specified per store.'
                );
            }
        } finally {
            $storeManager->setCurrentStore($currentStore);
        }
    }

    /**
     * @magentoDbIsolation disabled
     *
     * @magentoConfigFixture current_store catalog/frontend/flat_catalog_product 1
     * @magentoDataFixture Magento/Catalog/_files/dropdown_attribute.php
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     *
     * @return void
     */
    public function testCheckDropdownAttributeInFlat(): void
    {
        $attributeCode = 'dropdown_attribute';
        $options = $this->optionManagement->getItems($this->flatResource->getTypeId(), $attributeCode);
        $attributeValue = $options[1]->getValue();
        $this->updateProduct('simple2', $attributeCode, $attributeValue);
        $this->action->execute();
        $this->assertFlatColumnValue($attributeCode, $attributeValue);
    }

    /**
     * @magentoDbIsolation disabled
     *
     * @magentoConfigFixture default/catalog/product/flat/max_index_count 1
     *
     * @return void
     */
    public function testWithTooManyIndexes(): void
    {
        $indexesNeed = count($this->productIndexerHelper->getFlatIndexes());
        $message = (string)__(
            'The Flat Catalog module has a limit of %2$d filterable and/or sortable attributes.'
            . 'Currently there are %1$d of them.'
            . 'Please reduce the number of filterable/sortable attributes in order to use this module',
            $indexesNeed,
            1
        );
        $this->expectExceptionMessage($message);
        $this->expectException(LocalizedException::class);
        $this->action->execute();
    }

    /**
     * Assert if column exist and column value in flat table
     *
     * @param string $attributeCode
     * @param string $value
     * @return void
     */
    private function assertFlatColumnValue(string $attributeCode, string $value): void
    {
        $connect = $this->flatResource->getConnection();
        $tableName = $this->flatResource->getFlatTableName();
        $this->assertTrue($connect->tableColumnExists($tableName, $attributeCode));
        $select = $connect->select()->from($tableName, $attributeCode);
        $this->assertEquals($value, $connect->fetchOne($select));
    }

    /**
     * Update product
     *
     * @param string $sku
     * @param string $attributeCode
     * @param string $value
     * @return void
     */
    private function updateProduct(string $sku, string $attributeCode, string $value): void
    {
        $product = $this->productRepository->get($sku);
        $product->setData($attributeCode, $value);
        $this->productRepository->save($product);
    }
}
