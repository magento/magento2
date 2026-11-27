<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\OpenSearch\SearchAdapter;

use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Catalog\Test\Fixture\SelectAttribute as SelectAttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as ConfigurableAttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\Indexer\Test\Fixture\Indexer as IndexerFixture;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * A configurable product is sorted by its own value of a sortable dropdown, not by its children's values.
 */
class ConfigurableProductSortTest extends TestCase
{
    #[
        DbIsolation(false),
        AppIsolation(true),
        DataProvider('directionProvider'),
        DataFixture(
            SelectAttributeFixture::class,
            [
                'attribute_code' => 'custom_sort_order',
                'used_for_sort_by' => true,
                // '$sort_attr.rank_3$' resolves through getDataUsingMethod(), which turns 'rank3' into 'rank_3'
                'options' => ['rank_1', 'rank_2', 'rank_3', 'rank_4', 'rank_5'],
            ],
            'sort_attr'
        ),
        DataFixture(ConfigurableAttributeFixture::class, as: 'conf_attr'),
        DataFixture(CategoryFixture::class, as: 'category'),
        DataFixture(
            ProductFixture::class,
            ['sku' => 'simple-rank2', 'category_ids' => ['$category.id$'], 'custom_sort_order' => '$sort_attr.rank_2$'],
            'simple_rank2'
        ),
        DataFixture(
            ProductFixture::class,
            ['sku' => 'simple-rank4', 'category_ids' => ['$category.id$'], 'custom_sort_order' => '$sort_attr.rank_4$'],
            'simple_rank4'
        ),
        DataFixture(
            ProductFixture::class,
            ['sku' => 'child-rank1', 'visibility' => 1, 'custom_sort_order' => '$sort_attr.rank_1$'],
            'child_rank1'
        ),
        DataFixture(
            ProductFixture::class,
            ['sku' => 'child-rank5', 'visibility' => 1, 'custom_sort_order' => '$sort_attr.rank_5$'],
            'child_rank5'
        ),
        DataFixture(
            ConfigurableProductFixture::class,
            [
                'sku' => 'configurable-rank3',
                'category_ids' => ['$category.id$'],
                'custom_sort_order' => '$sort_attr.rank_3$',
                '_options' => ['$conf_attr$'],
                '_links' => ['$child_rank1$', '$child_rank5$'],
            ],
            'configurable'
        ),
        DataFixture(IndexerFixture::class),
    ]
    public function testConfigurableIsSortedByItsOwnValue(string $direction, array $expectedSkus): void
    {
        $category = DataFixtureStorageManager::getStorage()->get('category');
        $collection = Bootstrap::getObjectManager()->get('elasticsearchLayerCategoryItemCollectionProvider')
            ->getCollection($category);
        $collection->setOrder('custom_sort_order', $direction);

        $this->assertSame($expectedSkus, array_values($collection->getColumnValues('sku')));
    }

    /**
     * Children at rank1 and rank5 sort before and after every other product, so either direction exposes a sort
     * that reads them instead of the configurable's own rank3.
     *
     * @return array
     */
    public static function directionProvider(): array
    {
        return [
            'ascending' => ['asc', ['simple-rank2', 'configurable-rank3', 'simple-rank4']],
            'descending' => ['desc', ['simple-rank4', 'configurable-rank3', 'simple-rank2']],
        ];
    }
}
