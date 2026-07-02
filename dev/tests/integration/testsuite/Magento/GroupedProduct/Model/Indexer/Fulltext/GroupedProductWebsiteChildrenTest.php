<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GroupedProduct\Model\Indexer\Fulltext;

use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\GroupedProduct\Test\Fixture\Product as GroupedProductFixture;
use Magento\Store\Model\Store;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class GroupedProductWebsiteChildrenTest extends TestCase
{
    #[
        AppArea('frontend'),
        AppIsolation(true),
        DbIsolation(false),
        DataFixture(CategoryFixture::class, ['name' => 'Grouped Fulltext Category'], 'category'),
        DataFixture(
            ProductFixture::class,
            ['sku' => 'grouped-fulltext-orphan-child', 'website_ids' => [], 'category_ids' => ['$category.id$']],
            'orphan_child'
        ),
        DataFixture(
            ProductFixture::class,
            ['sku' => 'grouped-fulltext-valid-child', 'category_ids' => ['$category.id$']],
            'valid_child'
        ),
        DataFixture(
            ProductFixture::class,
            ['sku' => 'grouped-fulltext-simple-1', 'category_ids' => ['$category.id$']],
            'simple_1'
        ),
        DataFixture(
            ProductFixture::class,
            ['sku' => 'grouped-fulltext-simple-2', 'category_ids' => ['$category.id$']],
            'simple_2'
        ),
        DataFixture(
            GroupedProductFixture::class,
            [
                'sku' => 'grouped-fulltext-invalid-1',
                'category_ids' => ['$category.id$'],
                'product_links' => ['$orphan_child$']
            ],
            'invalid_grouped_1'
        ),
        DataFixture(
            GroupedProductFixture::class,
            [
                'sku' => 'grouped-fulltext-invalid-2',
                'category_ids' => ['$category.id$'],
                'product_links' => ['$orphan_child$']
            ],
            'invalid_grouped_2'
        ),
        DataFixture(
            GroupedProductFixture::class,
            [
                'sku' => 'grouped-fulltext-valid',
                'category_ids' => ['$category.id$'],
                'product_links' => ['$valid_child$']
            ],
            'valid_grouped'
        )
    ]
    public function testGroupedProductsWithoutWebsiteAssignedChildrenAreExcludedFromFulltextSize(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get(IndexerRegistry::class)->get(Fulltext::INDEXER_ID)->reindexAll();

        $collection = $objectManager->create(
            Collection::class,
            ['searchRequestName' => 'catalog_view_container']
        );
        $collection->setStoreId(Store::DISTRO_STORE_ID);
        $collection->addFieldToFilter(
            'category_ids',
            DataFixtureStorageManager::getStorage()->get('category')->getId()
        );

        $this->assertSame(4, $collection->getSize());
    }
}
