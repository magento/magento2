<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Category;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Store\Model\Store;
use Magento\Store\Test\Fixture\Group as StoreGroupFixture;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\Store\Test\Fixture\Website as WebsiteFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureBeforeTransaction;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;

class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            Collection::class
        );
        $this->fixtures = DataFixtureStorageManager::getStorage();
    }

    protected function tearDown(): void
    {
        /* Refresh stores memory cache after store deletion */
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Store\Model\StoreManagerInterface::class
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
        /** @var $category Category */
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
        $store = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(Store::class);
        $storeId = $store->load('second_category_store', 'code')->getId();
        $categories = $this->collection->setStoreId($storeId)->joinUrlRewrite()->addPathFilter('1/2/3');
        $this->assertCount(1, $categories);
        /** @var $category Category */
        $category = $categories->getFirstItem();
        $this->assertStringEndsWith('category-3-on-2.html', $category->getUrl());
    }

    /**
     * Test that product counts are loaded correctly for categories
     */
    #[
        DbIsolation(true),
        DataFixture(CategoryFixture::class, ['name' => 'Test Category'], as: 'category'),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'simple-product-1',
                'category_ids' => ['$category.id$']
            ],
            as: 'product1'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'simple-product-2',
                'category_ids' => ['$category.id$']
            ],
            as: 'product2'
        )
    ]
    public function testLoadProductCount()
    {
        $category = $this->fixtures->get('category');
        $objectManager = Bootstrap::getObjectManager();
        $collection = $objectManager->create(
            Collection::class
        );

        $collection->addIdFilter([$category->getId()]);
        $collection->setLoadProductCount(true);
        $collection->load();

        $this->assertCount(1, $collection);
        /** @var $loadedCategory Category */
        $loadedCategory = $collection->getFirstItem();
        $this->assertEquals($category->getId(), $loadedCategory->getId());
        $this->assertEquals(2, $loadedCategory->getProductCount(), 'Category should have 2 products');
    }

    /**
     * Test that product counts respect website filtering
     */
    #[
        DbIsolation(true),
        DataFixture(CategoryFixture::class, ['name' => 'Test Category 2'], as: 'category2'),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'simple-product-3',
                'category_ids' => ['$category2.id$']
            ],
            as: 'product3'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'simple-product-4',
                'category_ids' => ['$category2.id$']
            ],
            as: 'product4'
        )
    ]
    public function testLoadProductCountWithStoreFilter()
    {
        $category = $this->fixtures->get('category2');
        $objectManager = Bootstrap::getObjectManager();
        $collection = $objectManager->create(
            Collection::class
        );

        $collection->addIdFilter([$category->getId()]);
        $collection->setProductStoreId(Store::DEFAULT_STORE_ID);
        $collection->setLoadProductCount(true);
        $collection->load();

        $this->assertCount(1, $collection);
        /** @var $loadedCategory Category */
        $loadedCategory = $collection->getFirstItem();
        $this->assertEquals($category->getId(), $loadedCategory->getId());
        $this->assertEquals(
            2,
            $loadedCategory->getProductCount(),
            'Category should have 2 products for default store'
        );
    }

    /**
     * Test that product counts work correctly with multi-website setup
     */
    #[
        DbIsolation(true),
        DataFixtureBeforeTransaction(WebsiteFixture::class, as: 'website2'),
        DataFixtureBeforeTransaction(StoreGroupFixture::class, ['website_id' => '$website2.id$'], as: 'group2'),
        DataFixtureBeforeTransaction(
            StoreFixture::class,
            ['website_id' => '$website2.id$', 'group_id' => '$group2.id$'],
            as: 'store2'
        ),
        DataFixture(CategoryFixture::class, ['name' => 'Multi-Website Category'], as: 'category3'),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'product-website-1',
                'category_ids' => ['$category3.id$'],
                'extension_attributes' => [
                    // Assign to default website (ID: 1)
                    'website_ids' => [1]
                ]
            ],
            as: 'product_web1'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'product-website-2',
                'category_ids' => ['$category3.id$'],
                'extension_attributes' => [
                    'website_ids' => ['$website2.id$']
                ]
            ],
            as: 'product_web2'
        )
    ]
    public function testLoadProductCountMultiWebsite()
    {
        $category = $this->fixtures->get('category3');
        $store2 = $this->fixtures->get('store2');
        $objectManager = Bootstrap::getObjectManager();

        // Test product count for default website (should have 1 product)
        // Using DEFAULT_STORE_ID which corresponds to the default website
        $collection1 = $objectManager->create(Collection::class);
        $collection1->addIdFilter([$category->getId()]);
        $collection1->setProductStoreId(Store::DEFAULT_STORE_ID);
        $collection1->setLoadProductCount(true);
        $collection1->load();

        $this->assertCount(1, $collection1);
        /** @var $loadedCategory1 Category */
        $loadedCategory1 = $collection1->getFirstItem();
        $this->assertEquals(
            1,
            $loadedCategory1->getProductCount(),
            'Category should have 1 product for default website'
        );

        // Test product count for second website (should have 1 product)
        $collection2 = $objectManager->create(Collection::class);
        $collection2->addIdFilter([$category->getId()]);
        $collection2->setProductStoreId($store2->getId());
        $collection2->setLoadProductCount(true);
        $collection2->load();

        $this->assertCount(1, $collection2);
        /** @var $loadedCategory2 Category */
        $loadedCategory2 = $collection2->getFirstItem();
        $this->assertEquals(
            1,
            $loadedCategory2->getProductCount(),
            'Category should have 1 product for second website'
        );
    }
}
