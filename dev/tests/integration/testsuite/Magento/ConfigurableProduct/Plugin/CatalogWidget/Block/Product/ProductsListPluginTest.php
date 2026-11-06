<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Plugin\CatalogWidget\Block\Product;

use Magento\Catalog\Model\Indexer\Product\Eav\Processor;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\CatalogWidget\Block\Product\ProductsList;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as AttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

#[
    AppArea('frontend'),
    DbIsolation(false),
]
class ProductsListPluginTest extends TestCase
{
    /**
     * @var ProductsList
     */
    private ProductsList $block;

    /**
     * @var DataFixtureStorage
     */
    private DataFixtureStorage $fixtures;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->block = $objectManager->create(ProductsList::class);
        $this->fixtures = $objectManager->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * A configurable product whose children match the widget condition but are not visible individually
     * takes their place in the widget, even when the configurable itself does not match the condition
     */
    #[
        DataFixture(CategoryFixture::class, ['is_anchor' => 0], 'category'),
        DataFixture(CategoryFixture::class, ['is_anchor' => 0], 'other_category'),
        DataFixture(
            ProductFixture::class,
            ['visibility' => Visibility::VISIBILITY_NOT_VISIBLE, 'category_ids' => ['$category.id$']],
            'child'
        ),
        DataFixture(ProductFixture::class, ['category_ids' => ['$category.id$']], 'simple'),
        DataFixture(AttributeFixture::class, as: 'attribute'),
        DataFixture(
            ConfigurableProductFixture::class,
            ['_options' => ['$attribute$'], '_links' => ['$child$'], 'category_ids' => ['$other_category.id$']],
            'configurable'
        ),
    ]
    public function testConfigurableReplacesItsNotVisibleChildren(): void
    {
        Bootstrap::getObjectManager()->get(Processor::class)->reindexAll();

        $categoryId = $this->fixtures->get('category')->getId();
        $encodedConditions = '^[`1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Combine`,'
            . '`aggregator`:`all`,`value`:`1`,`new_child`:``^],`1--1`:'
            . '^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,'
            . '`attribute`:`category_ids`,`operator`:`^[^]`,'
            . '`value`:[`' . $categoryId . '`]^]^]';

        $this->block->setData('conditions_encoded', $encodedConditions);
        $this->block->setData('products_count', 10);
        $this->block->setData('page_var_name', 'np');

        (new \ReflectionMethod($this->block, '_beforeToHtml'))->invoke($this->block);

        $this->assertEqualsCanonicalizing(
            [
                $this->fixtures->get('configurable')->getSku(),
                $this->fixtures->get('simple')->getSku(),
            ],
            $this->block->getProductCollection()->getColumnValues('sku')
        );
    }
}
