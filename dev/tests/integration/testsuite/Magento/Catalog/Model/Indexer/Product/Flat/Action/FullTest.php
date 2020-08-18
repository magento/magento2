<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Flat\Action;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Indexer\Product\Flat\Processor;
use Magento\Catalog\Model\Indexer\Product\Flat\State;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;

/**
 * Full reindex Test
 */
class FullTest extends \Magento\TestFramework\Indexer\TestCase
{
    /**
     * @var State
     */
    protected $_state;

    /**
     * @var Processor
     */
    protected $_processor;

    /**
     * @var ObjectManager
     */
    private $objectManager;

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
        $this->objectManager = Bootstrap::getObjectManager();
        $this->_state = $this->objectManager->get(State::class);
        $this->_processor = $this->objectManager->get(Processor::class);
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/frontend/flat_catalog_product 1
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testReindexAll()
    {
        $this->assertTrue($this->_state->isFlatEnabled());
        $this->_processor->reindexAll();

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
     */
    public function testReindexAllMultipleStores()
    {
        $this->assertTrue($this->_state->isFlatEnabled());
        $this->_processor->reindexAll();

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

        $storeManager->setCurrentStore($currentStore);
    }
}
