<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Category;

use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    private $collection;

    private CollectionFactory $categoryCollectionFactory;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\ResourceModel\Category\Collection::class
        );
        $objectManager = Bootstrap::getObjectManager();
        $this->categoryCollectionFactory = $objectManager->get(CollectionFactory::class);
    }

    protected function setDown()
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
        $store = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Store\Model\Store::class);
        $storeId = $store->load('second_category_store', 'code')->getId();
        $categories = $this->collection->setStoreId($storeId)->joinUrlRewrite()->addPathFilter('1/2/3');
        $this->assertCount(1, $categories);
        /** @var $category \Magento\Catalog\Model\Category */
        $category = $categories->getFirstItem();
        $this->assertStringEndsWith('category-3-on-2.html', $category->getUrl());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/Model/ResourceModel/_files/categories_with_products_large.php
     * @throws LocalizedException
     */
    public function testBulkProcessingModeIsTriggered()
    {
        /** @var CategoryCollection $collection */
        $collection = $this->categoryCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('name', ['like' => 'bulk_test_123%']);
        $collection->setLoadProductCount(true);
        $collection->load();

        $this->assertGreaterThan(
            400,
            $collection->count(),
            'Bulk limit path not triggered.'
        );

        foreach ($collection as $category) {
            $this->assertNotNull(
                $category->getProductCount(),
                'ProductCount missing for category ' . $category->getId()
            );
            $this->assertIsInt(
                $category->getProductCount(),
                'ProductCount is not int for category ' . $category->getId()
            );
            $this->assertGreaterThan(
                0,
                $category->getProductCount(),
                'Invalid product count value.'
            );
        }
    }
}
