<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Controller\Adminhtml\Category;

use Magento\Catalog\Api\CategoryListInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Indexer\Category\Product as CategoryIndexer;
use Magento\CatalogSearch\Model\Indexer\Fulltext as FulltextIndexer;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * @magentoAppArea adminhtml
 * @magentoDbIsolation disabled
 */
class SaveTest extends AbstractBackendController
{
    private $indexerSchedule = [];

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->changeIndexerSchedule(FulltextIndexer::INDEXER_ID, true);
        $this->changeIndexerSchedule(CategoryIndexer::INDEXER_ID, true);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $this->changeIndexerSchedule(FulltextIndexer::INDEXER_ID, $this->indexerSchedule[FulltextIndexer::INDEXER_ID]);
        $this->changeIndexerSchedule(CategoryIndexer::INDEXER_ID, $this->indexerSchedule[CategoryIndexer::INDEXER_ID]);

        parent::tearDown();
    }

    /**
     * Checks a case when indexers are invalidated if products for category were changed.
     *
     * @magentoConfigFixture current_store catalog/frontend/flat_catalog_category true
     * @magentoDataFixture Magento/Catalog/_files/category_product.php
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     */
    public function testExecute()
    {
        $fulltextIndexer = $this->getIndexer(FulltextIndexer::INDEXER_ID);
        self::assertTrue($fulltextIndexer->isValid(), 'Fulltext indexer should be valid.');
        $categoryIndexer = $this->getIndexer(CategoryIndexer::INDEXER_ID);
        self::assertTrue($categoryIndexer->isValid(), 'Category indexer should be valid.');

        $category = $this->getCategory('Category 1');
        $productIdList = $this->getProductIdList(['simple1', 'simple2', 'simple3']);
        $inputData = [
            'category_products' => json_encode(array_fill_keys($productIdList, [0, 1, 2])),
            'entity_id' => $category->getId(),
            'default_sort_by' => 'position'
        ];

        $this->getRequest()->setPostValue($inputData);
        $this->getRequest()->setMethod('POST');
        $this->dispatch('backend/catalog/category/save');
        $this->assertSessionMessages(
            self::equalTo(['You saved the category.']),
            MessageInterface::TYPE_SUCCESS
        );

        $fulltextIndexer = $this->getIndexer(FulltextIndexer::INDEXER_ID);
        self::assertTrue($fulltextIndexer->isInvalid(), 'Fulltext indexer should be invalidated.');
        $categoryIndexer = $this->getIndexer(CategoryIndexer::INDEXER_ID);
        self::assertTrue($categoryIndexer->isInvalid(), 'Category indexer should be invalidated.');
    }

    /**
     * Gets indexer from registry by ID.
     *
     * @param string $indexerId
     * @return IndexerInterface
     */
    private function getIndexer(string $indexerId): IndexerInterface
    {
        /** @var IndexerRegistry $indexerRegistry */
        $indexerRegistry = $this->_objectManager->get(IndexerRegistry::class);
        return $indexerRegistry->get($indexerId);
    }

    /**
     * Changes the scheduled state of indexer.
     *
     * @param string $indexerId
     * @param bool $isScheduled
     * @return void
     */
    private function changeIndexerSchedule(string $indexerId, bool $isScheduled): void
    {
        $indexer = $this->getIndexer($indexerId);
        if (!isset($this->indexerSchedule[$indexerId])) {
            $this->indexerSchedule[$indexerId] = $indexer->isScheduled();
            $indexer->reindexAll();
        }
        $indexer->setScheduled($isScheduled);
    }

    /**
     * Gets category by name.
     *
     * @param string $name
     * @return CategoryInterface
     */
    private function getCategory(string $name): CategoryInterface
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->_objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('name', $name)
            ->create();
        /** @var CategoryListInterface $repository */
        $repository = $this->_objectManager->get(CategoryListInterface::class);
        $items = $repository->getList($searchCriteria)
            ->getItems();

        return array_pop($items);
    }

    /**
     * Gets list of product ID by SKU.
     *
     * @param array $skuList
     * @return array
     */
    private function getProductIdList(array $skuList): array
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->_objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('sku', $skuList, 'in')
            ->create();

        /** @var ProductRepositoryInterface $repository */
        $repository = $this->_objectManager->get(ProductRepositoryInterface::class);
        $items = $repository->getList($searchCriteria)
            ->getItems();

        $idList = array_map(
            function (ProductInterface $item) {
                return $item->getId();
            },
            $items
        );

        return $idList;
    }
}
