<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Model;

use Magento\TestFramework\Fixture\Config;
use PHPUnit\Framework\TestCase;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Store\Api\Data\StoreInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\Checkout\Test\Fixture\SetBillingAddress;
use Magento\Checkout\Test\Fixture\SetShippingAddress;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod;
use Magento\Checkout\Test\Fixture\SetPaymentMethod;
use Magento\Checkout\Test\Fixture\PlaceOrder;
use Magento\Sales\Test\Fixture\Invoice;
use Magento\Sales\Test\Fixture\Creditmemo;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Store\Test\Fixture\Website;
use Magento\Store\Test\Fixture\Group;
use Magento\Store\Test\Fixture\Store;
use Magento\Catalog\Test\Fixture\Product;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\Quote\Test\Fixture\AddProductToCart;

/**
 * Integration test for complete workflow using proper fixtures:
 * Create website, store, and store view with numeric names -> Place orders -> Create credit memo -> Verify grid display
 *
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StoreWithNumericNameCreditmemoWorkflowTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var CreditmemoRepositoryInterface
     */
    private $creditmemoRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->creditmemoRepository = $this->objectManager->get(CreditmemoRepositoryInterface::class);
    }

    /**
     * Test complete workflow using proper fixtures with scope parameter
     *
     * @return void
     */
    #[
        Config('general/country/allow', 'US', 'default'),
        Config('general/country/default', 'US', 'default'),
        Config('carriers/flatrate/active', '1', 'store', 'default'),
        Config('carriers/flatrate/price', '5.00', 'store', 'default'),
        Config('payment/checkmo/active', '1', 'store', 'default'),
        DataFixture(Website::class, ['code' => 'test_website', 'name' => '123test Website'], 'test_website'),
        DataFixture(
            Group::class,
            ['code' => 'test_group', 'name' => '123test Store Group', 'website_id' => '$test_website.id$'],
            'test_group'
        ),
        DataFixture(
            Store::class,
            [
                'code' => 'test_store',
                'name' => '123test Store View',
                'website_id' => '$test_website.id$',
                'group_id' => '$test_group.id$'
            ],
            'test_store'
        ),
        DataFixture(
            Product::class,
            ['sku' => 'simple-product-numeric-store', 'price' => 10, 'website_ids' => [1, '$test_website.id$']],
            'product'
        ),
        DataFixture(
            Customer::class,
            [
                'email' => 'customer@123test.com',
                'website_id' => '$test_website.id$',
                'store_id' => '$test_store.id$',
                'addresses' => [[]]
            ],
            'customer'
        ),
        DataFixture(
            CustomerCart::class,
            ['customer_id' => '$customer.id$'],
            as: 'quote',
            scope: 'test_store'
        ),
        DataFixture(
            AddProductToCart::class,
            ['cart_id' => '$quote.id$', 'product_id' => '$product.id$', 'qty' => 2]
        ),
        DataFixture(SetBillingAddress::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetShippingAddress::class, ['cart_id' => '$quote.id$']),
        DataFixture(
            SetDeliveryMethod::class,
            ['cart_id' => '$quote.id$', 'carrier_code' => 'flatrate', 'method_code' => 'flatrate']
        ),
        DataFixture(SetPaymentMethod::class, ['cart_id' => '$quote.id$']),
        DataFixture(PlaceOrder::class, ['cart_id' => '$quote.id$'], 'order'),
        DataFixture(Invoice::class, ['order_id' => '$order.id$']),
        DataFixture(
            Creditmemo::class,
            ['order_id' => '$order.id$', 'items' => [['qty' => 1, 'product_id' => '$product.id$']]],
            'creditmemo'
        )
    ]
    public function testCompleteWorkflowWithNumericStoreNamesUsingFixtures(): void
    {
        // Get fixtures
        $fixtures = DataFixtureStorageManager::getStorage();
        /** @var StoreInterface $store */
        $store = $fixtures->get('test_store');
        /** @var OrderInterface $order */
        $order = $fixtures->get('order');
        /** @var CreditmemoInterface $creditmemo */
        $creditmemo = $fixtures->get('creditmemo');

        // Verify order is in the correct store
        $this->assertEquals(
            $store->getId(),
            $order->getStoreId(),
            'Order should be placed in the test store'
        );

        // Verify credit memo is in the correct store
        $this->assertEquals(
            $store->getId(),
            $creditmemo->getStoreId(),
            'Credit memo should be in test store'
        );

        // Verify credit memo displays correctly in grid
        $this->verifyCreditMemoGridDisplaysRecords($creditmemo, $order, $store);
    }

    /**
     * Verify credit memo grid displays records correctly
     *
     * @param CreditmemoInterface $creditmemo
     * @param OrderInterface $order
     * @param StoreInterface $store
     * @return void
     */
    private function verifyCreditMemoGridDisplaysRecords(
        CreditmemoInterface $creditmemo,
        OrderInterface $order,
        StoreInterface $store
    ): void {
        // Test credit memo retrieval by order ID
        $creditmemoByOrder = $this->getCreditmemosByFilter('order_id', $order->getId());
        $this->assertCount(1, $creditmemoByOrder, 'Should find exactly one credit memo for the order');
        $foundCreditmemo = reset($creditmemoByOrder);

        // Assert that found credit memo matches expected values
        $this->assertEquals($creditmemo->getId(), $foundCreditmemo->getId());
        $this->assertEquals($creditmemo->getIncrementId(), $foundCreditmemo->getIncrementId());
        $this->assertEquals($order->getId(), $foundCreditmemo->getOrderId());
        $this->assertEquals($order->getStoreId(), $foundCreditmemo->getStoreId());

        // Test credit memo retrieval by creditmemo ID
        $creditmemoById = $this->getCreditmemosByFilter('entity_id', $creditmemo->getId());
        $this->assertCount(1, $creditmemoById, 'Credit memo should be found when filtering by ID');

        $foundCreditmemoById = reset($creditmemoById);
        $this->assertEquals($creditmemo->getId(), $foundCreditmemoById->getId());
        $this->assertEquals($order->getStoreId(), $foundCreditmemoById->getStoreId());

        // Explicitly verify the credit memo is created in the correct store
        $this->assertEquals(
            $store->getId(),
            $creditmemo->getStoreId(),
            'Credit memo should be created in the test store'
        );

        // Verify store name starts with numeric characters
        $this->assertEquals(
            '123test Store View',
            $store->getName(),
            'Test store should have numeric name'
        );

        // Verify credit memo is visible when filtering by test store
        $creditmemosByTestStore = $this->getCreditmemosByFilter('store_id', $store->getId());
        $this->assertGreaterThan(
            0,
            count($creditmemosByTestStore),
            'Credit memo should be visible when filtering by test store'
        );

        // Verify our specific credit memo is in the store-filtered results
        $foundInStoreFilter = false;
        foreach ($creditmemosByTestStore as $cm) {
            if ($cm->getId() === $creditmemo->getId()) {
                $foundInStoreFilter = true;
                break;
            }
        }
        $this->assertTrue(
            $foundInStoreFilter,
            sprintf('Credit memo should be found when filtering grid by "%s"', $store->getName())
        );
    }

    /**
     * Get credit memos by filter field and value
     *
     * @param string $field
     * @param mixed $value
     * @return array
     */
    private function getCreditmemosByFilter(string $field, mixed $value): array
    {
        $searchCriteria = $this->objectManager->get(SearchCriteriaBuilder::class)
            ->addFilter($field, $value)
            ->create();

        return $this->creditmemoRepository->getList($searchCriteria)->getItems();
    }
}
