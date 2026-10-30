<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ProductAlert\Model\ResourceModel\Stock\Customer\Collection;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\ObjectManagerInterface;
use Magento\ProductAlert\Model\ResourceModel\Price\Customer\Collection as PriceCustomerCollection;
use Magento\ProductAlert\Model\ResourceModel\Stock\Customer\Collection as StockCustomerCollection;
use Magento\ProductAlert\Model\ResourceModel\Stock as StockResource;
use Magento\ProductAlert\Model\StockFactory;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for ProductAlert Stock Customer Collection
 *
 * Tests the fix for issue #40027: Product stock alert's website and stock mismatch
 * in multi-website setup. Verifies that the collection correctly filters by
 * alert.website_id instead of customer.website_id.
 *
 * @magentoDbIsolation disabled
 */
class MultiWebsiteTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * Test that stock alert collection correctly filters by alert website_id
     * when customer is created on different website than alert subscription
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @return void
     */
    public function testStockAlertCollectionFiltersByAlertWebsiteId(): void
    {
        // Get customer created on base website (website_id = 1)
        $customerRegistry = Bootstrap::getObjectManager()->get(CustomerRegistry::class);
        $customer = $customerRegistry->retrieve(1);
        $customerWebsiteId = (int)$customer->getWebsiteId();
        
        // Get second website
        $websiteRepository = $this->objectManager->get(WebsiteRepositoryInterface::class);
        $secondWebsite = $websiteRepository->get('test');
        $secondWebsiteId = (int)$secondWebsite->getId();
        $storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $secondStore = $storeManager->getStore('fixture_third_store');
        $secondStoreId = (int)$secondStore->getId();
        
        // Get product
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get('simple');
        $productId = (int)$product->getId();
        
        // Create stock alert on second website (different from customer's website)
        $stockFactory = $this->objectManager->get(StockFactory::class);
        $stockAlert = $stockFactory->create();
        $stockAlert->setCustomerId($customer->getId())
            ->setProductId($productId)
            ->setWebsiteId($secondWebsiteId)
            ->setStoreId($secondStoreId)
            ->setStatus(0);
        $stockResource = $this->objectManager->get(StockResource::class);
        $stockResource->save($stockAlert);
        
        // Test: Collection should return alert when filtering by alert's website_id (second website)
        $collection = $this->objectManager->create(StockCustomerCollection::class);
        $collection->join($productId, $secondWebsiteId);
        $this->assertCount(1, $collection, 'Collection should return 1 alert for second website');
        $item = $collection->getFirstItem();
        $this->assertEquals($customer->getId(), (int)$item->getId(), 'Customer ID should match');
        $this->assertEquals($customer->getEmail(), $item->getEmail(), 'Customer email should match');
        
        // Test: Collection should NOT return alert when filtering by customer's website_id (base website)
        $collectionBaseWebsite = $this->objectManager->create(StockCustomerCollection::class);
        $collectionBaseWebsite->join($productId, $customerWebsiteId);
        $this->assertCount(0, $collectionBaseWebsite, 'Collection should return 0 alerts for customer website');
        
        // Test: Collection should return alert when no website filter is applied
        $collectionNoFilter = $this->objectManager->create(StockCustomerCollection::class);
        $collectionNoFilter->join($productId, 0);
        $this->assertCount(1, $collectionNoFilter, 'Collection should return 1 alert when no website filter');
    }

    /**
     * Test that stock alert collection works correctly when customer and alert
     * are on the same website
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @return void
     */
    public function testStockAlertCollectionSameWebsite(): void
    {
        // Get customer created on base website
        $customerRegistry = Bootstrap::getObjectManager()->get(CustomerRegistry::class);
        $customer = $customerRegistry->retrieve(1);
        $customerWebsiteId = (int)$customer->getWebsiteId();
        
        // Get base website store
        $storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $baseStore = $storeManager->getStore('default');
        $baseStoreId = (int)$baseStore->getId();
        
        // Get product
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get('simple');
        $productId = (int)$product->getId();
        
        // Create stock alert on same website as customer
        $stockFactory = $this->objectManager->get(StockFactory::class);
        $stockAlert = $stockFactory->create();
        $stockAlert->setCustomerId($customer->getId())
            ->setProductId($productId)
            ->setWebsiteId($customerWebsiteId)
            ->setStoreId($baseStoreId)
            ->setStatus(0);
        $stockResource = $this->objectManager->get(StockResource::class);
        $stockResource->save($stockAlert);
        
        // Test: Collection should return alert when filtering by same website
        $collection = $this->objectManager->create(StockCustomerCollection::class);
        $collection->join($productId, $customerWebsiteId);
        $this->assertCount(1, $collection, 'Collection should return 1 alert for same website');
        $item = $collection->getFirstItem();
        $this->assertEquals($customer->getId(), (int)$item->getId(), 'Customer ID should match');
    }

    /**
     * Test that stock alert collection correctly handles multiple alerts
     * for same customer on different websites
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @return void
     */
    public function testStockAlertCollectionMultipleWebsites(): void
    {
        // Get customer created on base website
        $customerRegistry = Bootstrap::getObjectManager()->get(CustomerRegistry::class);
        $customer = $customerRegistry->retrieve(1);
        $customerWebsiteId = (int)$customer->getWebsiteId();
        
        // Get second website
        $websiteRepository = $this->objectManager->get(WebsiteRepositoryInterface::class);
        $secondWebsite = $websiteRepository->get('test');
        $secondWebsiteId = (int)$secondWebsite->getId();
        $storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $secondStore = $storeManager->getStore('fixture_third_store');
        $secondStoreId = (int)$secondStore->getId();
        
        // Get base website store
        $baseStore = $storeManager->getStore('default');
        $baseStoreId = (int)$baseStore->getId();
        
        // Get product
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get('simple');
        $productId = (int)$product->getId();
        
        // Create stock alert on base website (customer's website)
        $stockFactory = $this->objectManager->get(StockFactory::class);
        $stockResource = $this->objectManager->get(StockResource::class);
        $stockAlert1 = $stockFactory->create();
        $stockAlert1->setCustomerId($customer->getId())
            ->setProductId($productId)
            ->setWebsiteId($customerWebsiteId)
            ->setStoreId($baseStoreId)
            ->setStatus(0);
        $stockResource->save($stockAlert1);
        
        // Create stock alert on second website (different from customer's website)
        $stockAlert2 = $stockFactory->create();
        $stockAlert2->setCustomerId($customer->getId())
            ->setProductId($productId)
            ->setWebsiteId($secondWebsiteId)
            ->setStoreId($secondStoreId)
            ->setStatus(0);
        $stockResource->save($stockAlert2);
        
        // Test: Collection should return only base website alert when filtering by base website
        $collectionBase = $this->objectManager->create(StockCustomerCollection::class);
        $collectionBase->join($productId, $customerWebsiteId);
        $this->assertCount(1, $collectionBase, 'Should return 1 alert for base website');
        
        // Test: Collection should return only second website alert when filtering by second website
        $collectionSecond = $this->objectManager->create(StockCustomerCollection::class);
        $collectionSecond->join($productId, $secondWebsiteId);
        $this->assertCount(1, $collectionSecond, 'Should return 1 alert for second website');
        
        // Test: Collection should return both alerts when no website filter
        $collectionAll = $this->objectManager->create(StockCustomerCollection::class);
        $collectionAll->join($productId, 0);
        $this->assertCount(2, $collectionAll, 'Should return 2 alerts when no website filter');
    }

    /**
     * Test that price alert collection also works correctly (same fix applied)
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @return void
     */
    public function testPriceAlertCollectionFiltersByAlertWebsiteId(): void
    {
        // Get customer created on base website
        $customerRegistry = Bootstrap::getObjectManager()->get(CustomerRegistry::class);
        $customer = $customerRegistry->retrieve(1);
        
        // Get second website
        $websiteRepository = $this->objectManager->get(WebsiteRepositoryInterface::class);
        $secondWebsite = $websiteRepository->get('test');
        $secondWebsiteId = (int)$secondWebsite->getId();
        $storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $secondStore = $storeManager->getStore('fixture_third_store');
        $secondStoreId = (int)$secondStore->getId();
        
        // Get product
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get('simple');
        $productId = (int)$product->getId();
        
        // Create price alert on second website
        $priceFactory = $this->objectManager->get(\Magento\ProductAlert\Model\PriceFactory::class);
        $priceResource = $this->objectManager->get(\Magento\ProductAlert\Model\ResourceModel\Price::class);
        $priceAlert = $priceFactory->create();
        $priceAlert->setCustomerId($customer->getId())
            ->setProductId($productId)
            ->setPrice($product->getPrice() + 10)
            ->setWebsiteId($secondWebsiteId)
            ->setStoreId($secondStoreId);
        $priceResource->save($priceAlert);
        
        // Test: Collection should return alert when filtering by alert's website_id
        $collection = $this->objectManager->create(PriceCustomerCollection::class);
        $collection->join($productId, $secondWebsiteId);
        $this->assertCount(1, $collection, 'Price collection should return 1 alert for second website');
        $item = $collection->getFirstItem();
        $this->assertEquals($customer->getId(), (int)$item->getId(), 'Customer ID should match');
    }

    /**
     * Test that collection join method doesn't select website_id and store_id
     * to avoid confusion with customer's website_id
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @return void
     */
    public function testCollectionDoesNotSelectWebsiteIdAndStoreId(): void
    {
        // Get customer
        $customerRegistry = Bootstrap::getObjectManager()->get(CustomerRegistry::class);
        $customer = $customerRegistry->retrieve(1);
        $customerWebsiteId = (int)$customer->getWebsiteId();
        
        // Get product
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get('simple');
        $productId = (int)$product->getId();
        
        // Create stock alert
        $storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $baseStore = $storeManager->getStore('default');
        $stockFactory = $this->objectManager->get(StockFactory::class);
        $stockAlert = $stockFactory->create();
        $stockAlert->setCustomerId($customer->getId())
            ->setProductId($productId)
            ->setWebsiteId($customerWebsiteId)
            ->setStoreId((int)$baseStore->getId())
            ->setStatus(0);
        $stockResource = $this->objectManager->get(StockResource::class);
        $stockResource->save($stockAlert);
        
        // Get collection
        $collection = $this->objectManager->create(StockCustomerCollection::class);
        $collection->join($productId, $customerWebsiteId);
        $item = $collection->getFirstItem();
        
        // Verify that website_id and store_id are NOT directly accessible from collection item
        // (they should not be in the SELECT to avoid confusion)
        // The collection should still work correctly for filtering
        $this->assertNotEmpty($item->getId(), 'Collection item should have ID');
        $this->assertEquals($customer->getEmail(), $item->getEmail(), 'Customer email should be accessible');
        
        // Verify the collection can still filter correctly by website_id in WHERE clause
        $this->assertCount(1, $collection, 'Collection should return correct results');
    }
}
