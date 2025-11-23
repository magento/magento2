<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Category;

use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Catalog\Test\Fixture\CategoryTreeWithProducts as CategoryTreeWithProductsFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use PHPUnit\Framework\TestCase;

/**
 * Tests collection category
 *
 * @see \Magento\Catalog\Model\ResourceModel\Category\Collection
 */
class CollectionTest extends TestCase
{
    /**
     * @var Collection
     */
    private Collection $collection;

    /**
     * @var CollectionFactory
     */
    private CollectionFactory $categoryCollectionFactory;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->collection = Bootstrap::getObjectManager()->create(Collection::class);
        $this->categoryCollectionFactory = $objectManager->get(CollectionFactory::class);
    }

    protected function tearDown(): void
    {
        /* Refresh stores memory cache after store deletion */
        Bootstrap::getObjectManager()->get(
            StoreManagerInterface::class
        )->reinitStores();
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/Model/ResourceModel/_files/category_multiple_stores.php
     */
    public function testJoinUrlRewriteOnDefault()
    {
        $categories = $this->collection->joinUrlRewrite()->addPathFilter('1/2/3');
        $this->assertCount(1, $categories);
        /** @var $category \Magento\Catalog\Model\Category */
        $category = $categories->getFirstItem();
        $this->assertStringEndsWith('category.html', $category->getUrl());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/Model/ResourceModel/_files/category_multiple_stores.php
     */
    public function testJoinUrlRewriteNotOnDefaultStore()
    {
        $store = Bootstrap::getObjectManager()
            ->create(\Magento\Store\Model\Store::class);
        $storeId = $store->load('second_category_store', 'code')->getId();
        $categories = $this->collection->setStoreId($storeId)->joinUrlRewrite()->addPathFilter('1/2/3');
        $this->assertCount(1, $categories);
        /** @var $category \Magento\Catalog\Model\Category */
        $category = $categories->getFirstItem();
        $this->assertStringEndsWith('category-3-on-2.html', $category->getUrl());
    }

    #[
        DataFixture(CategoryFixture::class, ['name' => 'TC L1 Root', 'parent_id' => '2', 'is_anchor' => 1], 'c1'),
        DataFixture(CategoryFixture::class, ['name' => 'TC L2 A', 'parent_id' => '$c1.id$', 'is_anchor' => 1], 'c11'),
        DataFixture(CategoryFixture::class, ['name' => 'TC L2 B', 'parent_id' => '$c1.id$', 'is_anchor' => 1], 'c12'),
        DataFixture(CategoryFixture::class, ['name' => 'TC L2 C', 'parent_id' => '$c1.id$', 'is_anchor' => 0], 'c13'),
        DataFixture(CategoryFixture::class, ['name' => 'TC L3 A1', 'parent_id' => '$c11.id$', 'is_anchor' => 1], 'c1111'),
        DataFixture(CategoryFixture::class, ['name' => 'TC L3 A2', 'parent_id' => '$c11.id$', 'is_anchor' => 1], 'c1112'),
        DataFixture(CategoryFixture::class, ['name' => 'TC L3 C1', 'parent_id' => '$c13.id$', 'is_anchor' => 0], 'c1113'),

        DataFixture(ProductFixture::class, ['sku' => 'TP-1A', 'category_ids' => ['$c12.id$']], as: 'p1'),
        DataFixture(ProductFixture::class, ['sku' => 'TP-2A', 'category_ids' => ['$c1111.id$']], as: 'p2'),
        DataFixture(ProductFixture::class, ['sku' => 'TP-3B', 'category_ids' => ['$c1112.id$',  '$c1113.id$']], as: 'p3'),
        DataFixture(ProductFixture::class, ['sku' => 'TP-4B', 'category_ids' => ['$c1112.id$', '$c1113.id$']], as: 'p4'),

        AppArea('adminhtml'),
        DbIsolation(true),
        AppIsolation(true)
    ]
    public function testLoadProductCountWithoutIndex()
    {
        $collection = $this->categoryCollectionFactory->create();
        $collection->addAttributeToSelect(['name', 'is_anchor']);
        $collection->addAttributeToFilter('name', ['like' => 'TC L%']);
        $collection->setLoadProductCount(true);
        $collection->load();

        $expected = [
            'TC L1 Root' => 4,
            'TC L2 A'    => 3,
            'TC L2 B'    => 1,
            'TC L2 C'    => 0,
            'TC L3 A1'   => 1,
            'TC L3 A2'   => 2,
            'TC L3 C1'   => 2
        ];

        foreach ($collection as $category) {
            $name = $category->getName();
            $this->assertEquals(
                $expected[$name],
                (int)$category->getProductCount(),
                "Product count incorrect for category $name"
            );
        }
    }
}
