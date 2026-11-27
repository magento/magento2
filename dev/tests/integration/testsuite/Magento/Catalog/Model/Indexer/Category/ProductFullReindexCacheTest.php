<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Indexer\Category;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Indexer\Category\Product as CategoryProductIndexer;
use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\App\Area;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\PageCache\Model\Cache\Type as PageCacheType;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\Cache;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for full page cache behavior after catalog_category_product full reindex.
 *
 * Full reindex must not mark the full_page cache type as INVALIDATED. Category cache tags
 * (cat_c) must still be cleaned via the normal indexer CacheContext / DeferredCacheCleaner path.
 */
#[
    AppArea(Area::AREA_ADMINHTML),
    AppIsolation(true),
    DbIsolation(false),
    Cache('full_page', true),
    Config('system/full_page_cache/caching_application', '1'),
    DataFixture(CategoryFixture::class, as: 'category'),
    DataFixture(ProductFixture::class, ['category_ids' => ['$category.id$']], as: 'product1'),
    DataFixture(ProductFixture::class, ['category_ids' => ['$category.id$']], as: 'product2'),
]
class ProductFullReindexCacheTest extends TestCase
{
    private const CATEGORY_PRODUCT_CACHE_KEY = 'category_product_full_reindex_cat_c';

    private const UNRELATED_CACHE_KEY = 'category_product_full_reindex_unrelated';

    private const CACHE_DATA = 'seeded_fpc_data';

    private const UNRELATED_CACHE_TAG = 'test_unrelated_tag';

    /**
     * @var TypeListInterface
     */
    private $typeList;

    /**
     * @var PageCacheType
     */
    private $pageCache;

    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->typeList = $objectManager->get(TypeListInterface::class);
        $this->pageCache = $objectManager->get(PageCacheType::class);
        $this->indexerRegistry = $objectManager->get(IndexerRegistry::class);
    }

    /**
     * After full catalog_category_product reindex, full_page must not be in invalidated types
     * when starting from a clean state.
     */
    public function testFullPageIsNotInInvalidatedTypesAfterFullReindex(): void
    {
        $this->typeList->cleanType(PageCacheType::TYPE_IDENTIFIER);

        $this->assertArrayNotHasKey(
            PageCacheType::TYPE_IDENTIFIER,
            $this->typeList->getInvalidated(),
            'Precondition: full_page must start from a clean (not invalidated) state'
        );

        $this->reindexCatalogCategoryProduct();

        $this->assertArrayNotHasKey(
            PageCacheType::TYPE_IDENTIFIER,
            $this->typeList->getInvalidated(),
            'full_page must not be in invalidated types after catalog_category_product full reindex'
        );
    }

    /**
     * Full reindex must still flush FPC by Category::CACHE_TAG (cat_c) without invalidating
     * the full_page type. An unrelated tagged entry must survive, proving tag-based cleaning.
     *
     * This is the integration-level guarantee that CacheContext still receives cat_c on full
     * reindex: DeferredCacheCleaner flushes identities registered by the indexer.
     */
    public function testFullReindexFlushesCategoryCacheTagsWithoutInvalidatingFullPageType(): void
    {
        $this->typeList->cleanType(PageCacheType::TYPE_IDENTIFIER);

        $this->pageCache->save(
            self::CACHE_DATA,
            self::CATEGORY_PRODUCT_CACHE_KEY,
            [Category::CACHE_TAG]
        );
        $this->pageCache->save(
            self::CACHE_DATA,
            self::UNRELATED_CACHE_KEY,
            [self::UNRELATED_CACHE_TAG]
        );

        $this->assertSame(
            self::CACHE_DATA,
            $this->pageCache->load(self::CATEGORY_PRODUCT_CACHE_KEY),
            'Precondition: cat_c-tagged FPC entry must exist before reindex'
        );
        $this->assertSame(
            self::CACHE_DATA,
            $this->pageCache->load(self::UNRELATED_CACHE_KEY),
            'Precondition: unrelated-tagged FPC entry must exist before reindex'
        );

        $this->reindexCatalogCategoryProduct();

        $this->assertEmpty(
            $this->pageCache->load(self::CATEGORY_PRODUCT_CACHE_KEY),
            'FPC entries tagged with cat_c must be cleaned after catalog_category_product full reindex'
        );
        $this->assertSame(
            self::CACHE_DATA,
            $this->pageCache->load(self::UNRELATED_CACHE_KEY),
            'FPC entries without cat_c must not be cleaned by catalog_category_product full reindex'
        );
        $this->assertArrayNotHasKey(
            PageCacheType::TYPE_IDENTIFIER,
            $this->typeList->getInvalidated(),
            'full_page must not be marked invalidated when tag-based flush runs on full reindex'
        );
    }

    /**
     * Run full reindex of catalog_category_product via public indexer API.
     */
    private function reindexCatalogCategoryProduct(): void
    {
        $this->indexerRegistry->get(CategoryProductIndexer::INDEXER_ID)->reindexAll();
    }
}
