<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Model\Plugin;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Indexer\Category\Product as CategoryProductIndexer;
use Magento\Catalog\Model\Indexer\Product\Category as ProductCategoryIndexer;
use Magento\Catalog\Model\Layer\Category as CategoryLayer;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Processor;
use Magento\Framework\Indexer\StateInterface;
use Magento\Indexer\Model\Processor as IndexerProcessor;
use Magento\Indexer\Test\Fixture\ScheduleMode;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureBeforeTransaction;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Test for category repository plugin
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoryTest extends TestCase
{
    /**
     * @var Processor
     */
    private $indexerProcessor;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->indexerProcessor = Bootstrap::getObjectManager()->create(Processor::class);
        $this->categoryRepository = Bootstrap::getObjectManager()->create(CategoryRepositoryInterface::class);
        $this->categoryCollectionFactory = Bootstrap::getObjectManager()->create(CategoryCollectionFactory::class);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/indexer_catalog_category.php
     * @magentoAppArea adminhtml
     */
    public function testIndexerInvalidatedAfterCategoryDelete()
    {
        $this->indexerProcessor->reindexAll();
        $isIndexerValid = (bool)$this->indexerProcessor->getIndexer()->isValid();

        $category = $this->getCategories(1);
        $this->categoryRepository->delete(array_pop($category));

        $state = $this->indexerProcessor->getIndexer()->getState();
        $state->loadByIndexer($this->indexerProcessor->getIndexerId());
        $status = $state->getStatus();

        $this->assertTrue($isIndexerValid);
        $this->assertEquals(StateInterface::STATUS_INVALID, $status);
    }

    /**
     * Anchor categories disabled globally and enabled on a store view must list products of their children
     *
     * @param string[] $saveOrder
     */
    #[
        AppIsolation(true),
        DbIsolation(false),
        DataProvider('enableOrderDataProvider'),
        DataFixtureBeforeTransaction(ScheduleMode::class, ['indexer' => CategoryProductIndexer::INDEXER_ID]),
        DataFixtureBeforeTransaction(ScheduleMode::class, ['indexer' => ProductCategoryIndexer::INDEXER_ID]),
        DataFixtureBeforeTransaction(ScheduleMode::class, ['indexer' => Processor::INDEXER_ID]),
        DataFixture(CategoryFixture::class, ['is_active' => false, 'is_anchor' => 1], 'parent'),
        DataFixture(
            CategoryFixture::class,
            ['parent_id' => '$parent.id$', 'is_active' => false, 'is_anchor' => 1],
            'child'
        ),
        DataFixture(ProductFixture::class, ['category_ids' => ['$child.id$']], 'product'),
    ]
    public function testProductIsFoundInAnchorCategoriesEnabledOnStoreView(array $saveOrder): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $parentId = (int)$fixtures->get('parent')->getId();
        $productId = (int)$fixtures->get('product')->getId();
        $storeId = $this->getDefaultStoreId();
        $this->updateMview();
        $this->assertNotContains($productId, $this->getProductIdsFoundInCategory($parentId, $storeId));

        foreach ($saveOrder as $categoryAlias) {
            $this->saveCategory((int)$fixtures->get($categoryAlias)->getId(), $storeId, ['is_active' => 1]);
        }
        $this->updateMview();

        $this->assertContains($productId, $this->getProductIdsFoundInCategory($parentId, $storeId));
    }

    /**
     * @return array
     */
    public static function enableOrderDataProvider(): array
    {
        return [
            'parent first' => [['parent', 'child']],
            'child first' => [['child', 'parent']],
        ];
    }

    /**
     * A product removed from a category in the same save that changes is_anchor must leave the category
     */
    #[
        AppIsolation(true),
        DbIsolation(false),
        DataFixtureBeforeTransaction(ScheduleMode::class, ['indexer' => CategoryProductIndexer::INDEXER_ID]),
        DataFixtureBeforeTransaction(ScheduleMode::class, ['indexer' => ProductCategoryIndexer::INDEXER_ID]),
        DataFixtureBeforeTransaction(ScheduleMode::class, ['indexer' => Processor::INDEXER_ID]),
        DataFixture(CategoryFixture::class, ['is_anchor' => 0], 'category'),
        DataFixture(ProductFixture::class, ['category_ids' => ['$category.id$']], 'removed'),
        DataFixture(ProductFixture::class, ['category_ids' => ['$category.id$']], 'kept'),
    ]
    public function testProductRemovedTogetherWithAnchorChangeIsNotFoundInCategory(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $categoryId = (int)$fixtures->get('category')->getId();
        $removedId = (int)$fixtures->get('removed')->getId();
        $keptId = (int)$fixtures->get('kept')->getId();
        $storeId = $this->getDefaultStoreId();
        $this->updateMview();
        $this->assertContains($removedId, $this->getProductIdsFoundInCategory($categoryId, $storeId));

        $this->saveCategory(
            $categoryId,
            Store::DEFAULT_STORE_ID,
            ['is_anchor' => 1, 'posted_products' => [$keptId => 0]]
        );
        $this->updateMview();

        $foundIds = $this->getProductIdsFoundInCategory($categoryId, $storeId);
        $this->assertNotContains($removedId, $foundIds);
        $this->assertContains($keptId, $foundIds);
    }

    /**
     * Products of a category disabled on a store view must not be found in it any more
     */
    #[
        AppIsolation(true),
        DbIsolation(false),
        DataFixtureBeforeTransaction(ScheduleMode::class, ['indexer' => CategoryProductIndexer::INDEXER_ID]),
        DataFixtureBeforeTransaction(ScheduleMode::class, ['indexer' => ProductCategoryIndexer::INDEXER_ID]),
        DataFixtureBeforeTransaction(ScheduleMode::class, ['indexer' => Processor::INDEXER_ID]),
        DataFixture(CategoryFixture::class, as: 'category'),
        DataFixture(ProductFixture::class, ['category_ids' => ['$category.id$']], 'product'),
    ]
    public function testProductIsNotFoundInCategoryDisabledOnStoreView(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $categoryId = (int)$fixtures->get('category')->getId();
        $productId = (int)$fixtures->get('product')->getId();
        $storeId = $this->getDefaultStoreId();
        $this->updateMview();
        $this->assertContains($productId, $this->getProductIdsFoundInCategory($categoryId, $storeId));

        $this->saveCategory($categoryId, $storeId, ['is_active' => 0]);
        $this->updateMview();

        $this->assertNotContains($productId, $this->getProductIdsFoundInCategory($categoryId, $storeId));
    }

    /**
     * Products of child categories must not be found in an anchor category disabled on a store view
     */
    #[
        AppIsolation(true),
        DbIsolation(false),
        DataFixtureBeforeTransaction(ScheduleMode::class, ['indexer' => CategoryProductIndexer::INDEXER_ID]),
        DataFixtureBeforeTransaction(ScheduleMode::class, ['indexer' => ProductCategoryIndexer::INDEXER_ID]),
        DataFixtureBeforeTransaction(ScheduleMode::class, ['indexer' => Processor::INDEXER_ID]),
        DataFixture(CategoryFixture::class, ['is_anchor' => 1], 'parent'),
        DataFixture(CategoryFixture::class, ['parent_id' => '$parent.id$', 'is_anchor' => 1], 'child'),
        DataFixture(ProductFixture::class, ['category_ids' => ['$child.id$']], 'product'),
    ]
    public function testChildProductIsNotFoundInAnchorCategoryDisabledOnStoreView(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $parentId = (int)$fixtures->get('parent')->getId();
        $productId = (int)$fixtures->get('product')->getId();
        $storeId = $this->getDefaultStoreId();
        $this->updateMview();
        $this->assertContains($productId, $this->getProductIdsFoundInCategory($parentId, $storeId));

        $this->saveCategory($parentId, $storeId, ['is_active' => 0]);
        $this->updateMview();

        $this->assertNotContains($productId, $this->getProductIdsFoundInCategory($parentId, $storeId));
    }

    /**
     * Process the indexer changelogs the same way the indexer_update_all_views cron job does
     *
     * The category products view queues products for the catalog search view, which may already be processed
     * in the same run, so the changelogs are processed twice, as two consecutive cron runs would do.
     *
     * @return void
     */
    private function updateMview(): void
    {
        $indexerProcessor = Bootstrap::getObjectManager()->create(IndexerProcessor::class);
        $indexerProcessor->updateMview();
        $indexerProcessor->updateMview();
    }

    /**
     * Save category data on the given scope through the category resource model
     *
     * @param int $categoryId
     * @param int $storeId
     * @param array $data
     * @return void
     */
    private function saveCategory(int $categoryId, int $storeId, array $data): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $category = $objectManager->create(CategoryFactory::class)->create();
        $category->setStoreId($storeId);
        $categoryResource = $objectManager->get(CategoryResource::class);
        $categoryResource->load($category, $categoryId);
        $category->addData($data);
        $categoryResource->save($category);
    }

    /**
     * Get IDs of the products the storefront finds in the category through the search engine
     *
     * @param int $categoryId
     * @param int $storeId
     * @return int[]
     */
    private function getProductIdsFoundInCategory(int $categoryId, int $storeId): array
    {
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get(StoreManagerInterface::class)->setCurrentStore($storeId);
        $category = $objectManager->create(CategoryFactory::class)->create();
        $category->setStoreId($storeId);
        $objectManager->get(CategoryResource::class)->load($category, $categoryId);
        $layer = $objectManager->create(CategoryLayer::class);
        $layer->setCurrentCategory($category);

        // getAllIds() would skip the search request, so take the IDs of the loaded search results
        return array_map('intval', array_keys($layer->getProductCollection()->getItems()));
    }

    /**
     * @return int
     */
    private function getDefaultStoreId(): int
    {
        return (int)Bootstrap::getObjectManager()->get(StoreManagerInterface::class)->getStore('default')->getId();
    }

    /**
     * Returns categories
     *
     * @param int $count
     * @return Category[]
     */
    private function getCategories(int $count): array
    {
        $collection = $this->categoryCollectionFactory->create()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('is_active')
            ->getItems();
        $result = array_slice($collection, 2);

        return array_slice($result, 0, $count);
    }
}
