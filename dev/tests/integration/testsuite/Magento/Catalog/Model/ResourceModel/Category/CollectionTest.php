<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Category;

class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    private $collection;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\ResourceModel\Category\Collection::class
        );
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
}
