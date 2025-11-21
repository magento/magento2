<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Category;

use Magento\Catalog\Test\Fixture\CategoryTreeWithProducts as CategoryTreeWithProductsFixture;
use Magento\Catalog\Test\Fixture\CategoryTree as CategoryTreeFixture;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Collection|mixed
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
        DataFixture (
            CategoryTreeWithProductsFixture ::class,
            [
                'category_identifier' => 'bulk_test_123_cat',
                'category_count' => 401,
                'product_identifier' => 'bulk_test_123_prd',
                'product_count' => 20,
                'depth' => 3
            ],
            'cats'
        ),
        AppArea('adminhtml'),
        DbIsolation(true),
        AppIsolation(true)
    ]
    public function testBulkProcessingModeIsTriggered()
    {
        /** @var CategoryCollection $collection */
        $collection = $this->categoryCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('name', ['like' => 'bulk_test_123_cat%']);
        $collection->setLoadProductCount(true);
        $collection->load();

        $this->assertGreaterThan(
            400,
            $collection->getSize(),
            'Bulk limit path not triggered.'
        );

        foreach ($collection as $category) {
            $productCount = $category->getProductCount();
            $this->assertNotNull(
                $productCount,
                'ProductCount missing for category ' . $category->getId()
            );
            $this->assertGreaterThan(
                0,
                $productCount,
                sprintf('Invalid product count for category %d.', $category->getId())
            );
        }
    }
}
