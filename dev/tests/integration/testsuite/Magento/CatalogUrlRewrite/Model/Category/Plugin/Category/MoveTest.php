<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Model\Category\Plugin\Category;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\Store as StoreModel;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use PHPUnit\Framework\TestCase;

/**
 * Verifies that moving a category recalculates store-view-scoped url_path values
 * for the moved category and its descendants instead of losing them - for every
 * store view that has an override, not just one of them.
 *
 * DB isolation is disabled because moving a category triggers a synchronous
 * category-product flat-index reindex, which cannot run nested inside the
 * transaction DB isolation wraps tests in; app isolation is enabled instead so
 * the extra store views created here don't leak into other tests.
 */
#[
    DbIsolation(false),
    AppIsolation(true),
]
class MoveTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->categoryRepository = $this->objectManager->get(CategoryRepositoryInterface::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->fixtures = DataFixtureStorageManager::getStorage();
    }

    #[
        DataFixture(StoreFixture::class, as: 'store2'),
        DataFixture(StoreFixture::class, as: 'store3'),
        DataFixture(CategoryFixture::class, ['url_key' => 'category-1'], as: 'category1'),
        DataFixture(
            CategoryFixture::class,
            ['url_key' => 'category-2', 'parent_id' => '$category1.id$'],
            as: 'category2'
        ),
        DataFixture(
            CategoryFixture::class,
            ['url_key' => 'category-3', 'parent_id' => '$category2.id$'],
            as: 'category3'
        ),
        DataFixture(CategoryFixture::class, ['url_key' => 'category-4'], as: 'category4'),
    ]
    public function testMoveRecalculatesStoreScopedUrlPathForDescendants(): void
    {
        $category1Id = (int)$this->fixtures->get('category1')->getId();
        $category2Id = (int)$this->fixtures->get('category2')->getId();
        $category3Id = (int)$this->fixtures->get('category3')->getId();
        $category4Id = (int)$this->fixtures->get('category4')->getId();

        // Two non-default store views, each with their own url_key override, so a
        // regression that only recalculates the first (or the last) one processed
        // is caught, not just a regression that drops every non-default store.
        $stores = [
            (int)$this->fixtures->get('store2')->getId() => '-tt',
            (int)$this->fixtures->get('store3')->getId() => '-uu',
        ];
        foreach ($stores as $storeId => $suffix) {
            $this->storeManager->setCurrentStore($storeId);
            foreach ([$category1Id, $category2Id, $category3Id] as $categoryId) {
                $scopedCategory = $this->categoryRepository->get($categoryId, $storeId);
                $scopedCategory->setUrlKey($scopedCategory->getUrlKey() . $suffix);
                $this->categoryRepository->save($scopedCategory);
            }
        }
        $this->storeManager->setCurrentStore(StoreModel::DEFAULT_STORE_ID);

        // In production, creating/editing categories and later moving one happen as
        // separate admin requests, so services implementing ResetAfterRequestInterface
        // start with an empty cache by the time a move happens. Reset them here too,
        // otherwise the repository's and children-provider's caches - populated above
        // while this category tree had no descendants/overrides yet - would still be
        // holding stale data for the move that follows.
        $this->objectManager->get(CategoryRepository::class)->_resetState();
        $this->objectManager->get(ChildrenCategoriesProvider::class)->_resetState();

        // Load the category in a non-default, non-LAST-processed store's scope,
        // simulating an admin performing the move while that store view is selected.
        // This is deliberately NOT the last store the loop processes (store3 is), so
        // it catches two distinct regressions: (a) refreshing this store's own entry
        // deleting stores processed earlier in the loop instead of refreshing them,
        // and (b) the category object being left with store3's url_key in memory
        // (mismatched with this store) when catalog_category_move_after regenerates
        // the storefront url_rewrite from that same object right after.
        $categoryStoreId = array_key_first($stores);
        $movedCategory = $this->categoryRepository->get($category1Id, $categoryStoreId);
        $movedCategory->move($category4Id, null);

        // Fetch through a fresh repository instance so store-scoped reads
        // aren't served from the shared repository's pre-move instance cache.
        $categoryRepository = $this->objectManager->create(CategoryRepositoryInterface::class);

        $this->assertSame(
            'category-4/category-1',
            $categoryRepository->get($category1Id, StoreModel::DEFAULT_STORE_ID)->getUrlPath(),
            "category1's default-scope url_path must be refreshed, not deleted, by the move"
        );

        foreach ($stores as $storeId => $suffix) {
            $this->assertSame(
                "category-4/category-1{$suffix}",
                $categoryRepository->get($category1Id, $storeId)->getUrlPath(),
                "category1's url_path was not recalculated for store {$storeId}"
            );
            $this->assertSame(
                "category-4/category-1{$suffix}/category-2{$suffix}",
                $categoryRepository->get($category2Id, $storeId)->getUrlPath(),
                "category2's url_path was not recalculated for store {$storeId}"
            );
            $this->assertSame(
                "category-4/category-1{$suffix}/category-2{$suffix}/category-3{$suffix}",
                $categoryRepository->get($category3Id, $storeId)->getUrlPath(),
                "category3's url_path was not recalculated for store {$storeId}"
            );
        }

        // The storefront url_rewrite for category1 must reflect $categoryStoreId's own
        // url_key, not another store's - catalog_category_move_after regenerates it
        // right after the plugin returns, from the same category object the plugin
        // used, so that object must be left internally consistent.
        $urlFinder = $this->objectManager->get(UrlFinderInterface::class);
        $canonicalRewrite = $urlFinder->findOneByData([
            UrlRewrite::ENTITY_TYPE => 'category',
            UrlRewrite::ENTITY_ID => $category1Id,
            UrlRewrite::STORE_ID => $categoryStoreId,
            UrlRewrite::IS_AUTOGENERATED => 1,
        ]);
        $this->assertNotNull(
            $canonicalRewrite,
            "No storefront url_rewrite found for category1 at store {$categoryStoreId}"
        );
        $this->assertStringStartsWith(
            "category-4/category-1{$stores[$categoryStoreId]}",
            $canonicalRewrite->getRequestPath(),
            'Storefront url_rewrite for category1 was generated from the wrong store\'s url_key'
        );
    }
}
