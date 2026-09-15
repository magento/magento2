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
use PHPUnit\Framework\TestCase;

/**
 * Verifies that moving a category recalculates store-view-scoped url_path values
 * for the moved category and its descendants instead of losing them.
 *
 * DB isolation is disabled because moving a category triggers a synchronous
 * category-product flat-index reindex, which cannot run nested inside the
 * transaction DB isolation wraps tests in; app isolation is enabled instead so
 * the extra store view created here doesn't leak into other tests.
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
        $secondStore = $this->fixtures->get('store2');
        $secondStoreId = (int)$secondStore->getId();
        $category1Id = (int)$this->fixtures->get('category1')->getId();
        $category2Id = (int)$this->fixtures->get('category2')->getId();
        $category3Id = (int)$this->fixtures->get('category3')->getId();
        $category4Id = (int)$this->fixtures->get('category4')->getId();

        $this->storeManager->setCurrentStore($secondStore);
        foreach ([$category1Id, $category2Id, $category3Id] as $categoryId) {
            $scopedCategory = $this->categoryRepository->get($categoryId, $secondStoreId);
            $scopedCategory->setUrlKey($scopedCategory->getUrlKey() . '-tt');
            $this->categoryRepository->save($scopedCategory);
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

        $movedCategory = $this->categoryRepository->get($category1Id);
        $movedCategory->move($category4Id, null);

        // Fetch through a fresh repository instance so store-scoped reads
        // aren't served from the shared repository's pre-move instance cache.
        $categoryRepository = $this->objectManager->create(CategoryRepositoryInterface::class);

        $this->assertSame(
            'category-4/category-1-tt',
            $categoryRepository->get($category1Id, $secondStoreId)->getUrlPath()
        );
        $this->assertSame(
            'category-4/category-1-tt/category-2-tt',
            $categoryRepository->get($category2Id, $secondStoreId)->getUrlPath()
        );
        $this->assertSame(
            'category-4/category-1-tt/category-2-tt/category-3-tt',
            $categoryRepository->get($category3Id, $secondStoreId)->getUrlPath()
        );
    }
}
