<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRuleGraphQl\Test\Unit\Plugin\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogRule\Model\ResourceModel\Rule;
use Magento\CatalogRule\Model\ResourceModel\RuleFactory;
use Magento\CatalogRule\Observer\PrepareCatalogProductCollectionPricesObserver;
use Magento\CatalogRule\Observer\RulePricesStorage;
use Magento\CatalogRule\Pricing\Price\CatalogRulePrice;
use Magento\CatalogRuleGraphQl\Plugin\Pricing\Price\UpdateCatalogRulePrice;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateCatalogRulePriceTest extends TestCase
{
    private const STORE_ID = 1;
    private const WEBSITE_ID = 1;
    private const CUSTOMER_GROUP_ID = 1;

    /**
     * @var Rule|MockObject
     */
    private $ruleResource;

    /**
     * @var TimezoneInterface&Stub
     */
    private $localeDate;

    /**
     * @var Store&Stub
     */
    private $store;

    /**
     * @var RulePricesStorage
     */
    private $rulePricesStorage;

    /**
     * @var UpdateCatalogRulePrice
     */
    private $plugin;

    protected function setUp(): void
    {
        $this->ruleResource = $this->createMock(Rule::class);
        $this->localeDate = self::createStub(TimezoneInterface::class);
        $this->localeDate->method('scopeDate')
            ->willReturnCallback(fn () => new \DateTime('2026-10-26 00:00:00'));
        $this->localeDate->method('scopeTimeStamp')->willReturn(strtotime('2026-10-26 14:30:00 UTC'));
        $this->store = self::createStub(Store::class);
        $this->store->method('getId')->willReturn(self::STORE_ID);
        $this->store->method('getWebsiteId')->willReturn(self::WEBSITE_ID);
        $this->rulePricesStorage = new RulePricesStorage();

        $this->plugin = new UpdateCatalogRulePrice(
            $this->localeDate,
            $this->ruleResource,
            $this->rulePricesStorage
        );
    }

    /**
     * Prices the collection prefetch stored are read without a query per product.
     *
     * @return void
     */
    public function testUsesRulePricesPrefetchedForCollection(): void
    {
        $productWithRule = $this->createProduct(10, self::CUSTOMER_GROUP_ID);
        $productWithoutRule = $this->createProduct(11, self::CUSTOMER_GROUP_ID);
        $this->ruleResource->expects($this->once())
            ->method('getRulePrices')
            ->willReturn([10 => '8.5000']);
        $this->ruleResource->expects($this->never())->method('getRulePrice');

        $this->prefetchRulePrices([$productWithRule, $productWithoutRule]);

        $this->assertSame(8.5, $this->plugin->afterGetValue($this->createSubject($productWithRule), false));
        $this->assertFalse($this->plugin->afterGetValue($this->createSubject($productWithoutRule), false));
    }

    /**
     * A product outside a prefetched collection still gets its rule price.
     *
     * @return void
     */
    public function testQueriesRulePriceWhenNotPrefetched(): void
    {
        $product = $this->createProduct(10, self::CUSTOMER_GROUP_ID);
        $this->ruleResource->expects($this->once())
            ->method('getRulePrice')
            ->with($this->isInstanceOf(\DateTime::class), self::WEBSITE_ID, self::CUSTOMER_GROUP_ID, 10)
            ->willReturn('7.0000');

        $this->assertSame(7.0, $this->plugin->afterGetValue($this->createSubject($product), false));
    }

    /**
     * Without a customer group on the product the original value is kept.
     *
     * @return void
     */
    public function testKeepsValueWithoutCustomerGroup(): void
    {
        $product = $this->createProduct(10, null);
        $this->ruleResource->expects($this->never())->method('getRulePrice');

        $this->assertSame(9.0, $this->plugin->afterGetValue($this->createSubject($product), 9.0));
    }

    /**
     * Run the core prepare_catalog_product_collection_prices observer for a logged-in customer.
     *
     * @param Product[] $products
     * @return void
     */
    private function prefetchRulePrices(array $products): void
    {
        $ruleFactory = self::createStub(RuleFactory::class);
        $ruleFactory->method('create')->willReturn($this->ruleResource);
        $storeManager = self::createStub(StoreManagerInterface::class);
        $storeManager->method('getStore')->willReturn($this->store);
        $customerSession = self::createStub(Session::class);
        $customerSession->method('isLoggedIn')->willReturn(true);
        $customerSession->method('getCustomerGroupId')->willReturn(self::CUSTOMER_GROUP_ID);
        $collection = self::createStub(Collection::class);
        $collection->method('getIterator')->willReturnCallback(fn () => new \ArrayIterator($products));

        $observer = new PrepareCatalogProductCollectionPricesObserver(
            $this->rulePricesStorage,
            $ruleFactory,
            $storeManager,
            $this->localeDate,
            $customerSession,
            self::createStub(GroupManagementInterface::class)
        );
        $observer->execute(
            new Observer(['event' => new Event(['collection' => $collection, 'store_id' => self::STORE_ID])])
        );
    }

    /**
     * @param int $productId
     * @param int|null $customerGroupId
     * @return Product
     */
    private function createProduct(int $productId, ?int $customerGroupId): Product
    {
        $product = self::getStubBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId', 'getStore'])
            ->getStub();
        $product->method('getId')->willReturn($productId);
        $product->method('getStore')->willReturn($this->store);
        $product->setData('customer_group_id', $customerGroupId);

        return $product;
    }

    /**
     * @param Product $product
     * @return CatalogRulePrice
     */
    private function createSubject(Product $product): CatalogRulePrice
    {
        $subject = self::createStub(CatalogRulePrice::class);
        $subject->method('getProduct')->willReturn($product);

        return $subject;
    }
}
