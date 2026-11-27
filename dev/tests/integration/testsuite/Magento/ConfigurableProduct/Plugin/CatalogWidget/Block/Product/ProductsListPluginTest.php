<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Plugin\CatalogWidget\Block\Product;

use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\CatalogWidget\Block\Product\ProductsList;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as ConfigurableAttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[
    CoversClass(ProductsListPlugin::class),
    AppArea('frontend'),
    DbIsolation(false),
]
class ProductsListPluginTest extends TestCase
{
    #[
        DataFixture(CategoryFixture::class, as: 'category'),
        DataFixture(ConfigurableAttributeFixture::class, as: 'attribute'),
        DataFixture(
            ProductFixture::class,
            ['visibility' => Visibility::VISIBILITY_NOT_VISIBLE, 'category_ids' => ['$category.id$']],
            'child1'
        ),
        DataFixture(
            ProductFixture::class,
            ['visibility' => Visibility::VISIBILITY_NOT_VISIBLE, 'category_ids' => ['$category.id$']],
            'child2'
        ),
        DataFixture(
            ConfigurableProductFixture::class,
            ['_options' => ['$attribute$'], '_links' => ['$child1$', '$child2$']],
            'configurable'
        ),
        DataFixture(ProductFixture::class, ['category_ids' => ['$category.id$']], 'simple'),
        DataFixture(ProductFixture::class, as: 'other'),
    ]
    public function testConfigurableParentIsListedWhenOnlyHiddenChildrenMatch(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $block = Bootstrap::getObjectManager()->create(ProductsList::class);
        $block->setData('products_count', 5);
        $block->setData(
            'conditions_encoded',
            '^[`1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Combine`,'
            . '`aggregator`:`all`,`value`:`1`,`new_child`:``^],'
            . '`1--1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,'
            . '`attribute`:`category_ids`,`operator`:`==`,'
            . '`value`:`' . $fixtures->get('category')->getId() . '`^]^]'
        );

        $collection = $block->createCollection();
        $collection->load();

        $this->assertEqualsCanonicalizing(
            [$fixtures->get('configurable')->getSku(), $fixtures->get('simple')->getSku()],
            $collection->getColumnValues('sku')
        );
    }
}
