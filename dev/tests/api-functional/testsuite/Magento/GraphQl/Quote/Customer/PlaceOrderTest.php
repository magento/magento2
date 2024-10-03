<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Customer;

use Magento\Catalog\Test\Fixture\ProductStock as ProductStockFixture;
use Magento\Checkout\Test\Fixture\SetGuestEmail as SetGuestEmailFixture;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Registry;
use Magento\Indexer\Test\Fixture\Indexer;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Quote\Test\Fixture\QuoteIdMask;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;

/**
 * Test for placing an order for customer
 */
class PlaceOrderTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var CollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedQuoteIdInterface;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->quoteIdToMaskedQuoteIdInterface = $objectManager->get(QuoteIdToMaskedQuoteIdInterface::class);
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
        $this->orderCollectionFactory = $objectManager->get(CollectionFactory::class);
        $this->orderRepository = $objectManager->get(OrderRepositoryInterface::class);
        $this->registry = Bootstrap::getObjectManager()->get(Registry::class);
    }

    #[
        Config('carriers/flatrate/active', '1', 'store', 'default'),
        Config('carriers/tablerate/active', '1', 'store', 'default'),
        Config('carriers/freeshipping/active', '1', 'store', 'default'),
        Config('payment/banktransfer/active', '1', 'store', 'default'),
        Config('payment/cashondelivery/active', '1', 'store', 'default'),
        Config('payment/checkmo/active', '1', 'store', 'default'),
        Config('payment/purchaseorder/active', '1', 'store', 'default'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(Indexer::class, as: 'indexer'),
        DataFixture(Customer::class, ['email' => 'customer@example.com'], as: 'customer'),
        DataFixture(
            CustomerCart::class,
            [
                'customer_id' => '$customer.id$',
                'reserved_order_id' => 'test_quote'
            ],
            'cart'
        ),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    public function testPlaceOrder()
    {
        $reservedOrderId = 'test_quote';
        $maskedQuoteId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();

        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());

        self::assertArrayHasKey('placeOrder', $response);
        self::assertArrayHasKey('order_number', $response['placeOrder']['order']);
        self::assertEquals($reservedOrderId, $response['placeOrder']['order']['order_number']);
    }

    #[
        DataFixture(Customer::class, ['email' => 'customer@example.com'], as: 'customer'),
    ]
    public function testPlaceOrderIfCartIdIsEmpty()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Required parameter "cart_id" is missing');

        $maskedQuoteId = '';
        $query = $this->getQuery($maskedQuoteId);

        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    #[
        DataFixture(Customer::class, ['email' => 'customer@example.com'], as: 'customer'),
        DataFixture(
            CustomerCart::class,
            [
                'customer_id' => '$customer.id$',
                'reserved_order_id' => 'test_quote'
            ],
            'cart'
        ),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    public function testPlaceOrderWithNoItemsInCart()
    {
        $maskedQuoteId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $query = $this->getQuery($maskedQuoteId);

        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());
        self::assertEquals(1, count($response['placeOrder']['errors']));
        self::assertEquals('UNABLE_TO_PLACE_ORDER', $response['placeOrder']['errors'][0]['code']);
        self::assertEquals(
            'A server error stopped your order from being placed. Please try to place your order again.',
            $response['placeOrder']['errors'][0]['message']
        );
    }

    #[
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(Indexer::class, as: 'indexer'),
        DataFixture(Customer::class, ['email' => 'customer@example.com'], as: 'customer'),
        DataFixture(
            CustomerCart::class,
            [
                'customer_id' => '$customer.id$',
                'reserved_order_id' => 'test_quote'
            ],
            'cart'
        ),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    public function testPlaceOrderWithNoShippingAddress()
    {
        $maskedQuoteId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $query = $this->getQuery($maskedQuoteId);

        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());
        self::assertEquals(1, count($response['placeOrder']['errors']));
        self::assertEquals('UNABLE_TO_PLACE_ORDER', $response['placeOrder']['errors'][0]['code']);
        self::assertEquals(
            'Some addresses can\'t be used due to the configurations for specific countries.',
            $response['placeOrder']['errors'][0]['message']
        );
    }

    #[
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(Indexer::class, as: 'indexer'),
        DataFixture(Customer::class, ['email' => 'customer@example.com'], as: 'customer'),
        DataFixture(
            CustomerCart::class,
            [
                'customer_id' => '$customer.id$',
                'reserved_order_id' => 'test_quote'
            ],
            'cart'
        ),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    public function testPlaceOrderWithNoShippingMethod()
    {
        $maskedQuoteId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $query = $this->getQuery($maskedQuoteId);

        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());
        self::assertEquals(1, count($response['placeOrder']['errors']));
        self::assertEquals('UNABLE_TO_PLACE_ORDER', $response['placeOrder']['errors'][0]['code']);
        self::assertEquals(
            'The shipping method is missing. Select the shipping method and try again.',
            $response['placeOrder']['errors'][0]['message']
        );
    }

    #[
        Config('carriers/flatrate/active', '1', 'store', 'default'),
        Config('carriers/tablerate/active', '1', 'store', 'default'),
        Config('carriers/freeshipping/active', '1', 'store', 'default'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(Indexer::class, as: 'indexer'),
        DataFixture(Customer::class, ['email' => 'customer@example.com'], as: 'customer'),
        DataFixture(
            CustomerCart::class,
            [
                'customer_id' => '$customer.id$',
                'reserved_order_id' => 'test_quote'
            ],
            'cart'
        ),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    public function testPlaceOrderWithNoBillingAddress()
    {
        $maskedQuoteId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $query = $this->getQuery($maskedQuoteId);

        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());
        self::assertEquals(1, count($response['placeOrder']['errors']));
        self::assertEquals('UNABLE_TO_PLACE_ORDER', $response['placeOrder']['errors'][0]['code']);
        self::assertStringContainsString(
            'Please check the billing address information.',
            $response['placeOrder']['errors'][0]['message']
        );
    }

    #[
        Config('carriers/flatrate/active', '1', 'store', 'default'),
        Config('carriers/tablerate/active', '1', 'store', 'default'),
        Config('carriers/freeshipping/active', '1', 'store', 'default'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(Indexer::class, as: 'indexer'),
        DataFixture(Customer::class, ['email' => 'customer@example.com'], as: 'customer'),
        DataFixture(
            CustomerCart::class,
            [
                'customer_id' => '$customer.id$',
                'reserved_order_id' => 'test_quote'
            ],
            'cart'
        ),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    public function testPlaceOrderWithNoPaymentMethod()
    {
        $maskedQuoteId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $query = $this->getQuery($maskedQuoteId);

        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());
        self::assertEquals(1, count($response['placeOrder']['errors']));
        self::assertEquals('UNABLE_TO_PLACE_ORDER', $response['placeOrder']['errors'][0]['code']);
        self::assertEquals(
            'Enter a valid payment method and try again.',
            $response['placeOrder']['errors'][0]['message']
        );
    }

    #[
        Config('carriers/flatrate/active', '1', 'store', 'default'),
        Config('carriers/tablerate/active', '1', 'store', 'default'),
        Config('carriers/freeshipping/active', '1', 'store', 'default'),
        Config('cataloginventory/options/enable_inventory_check', 1),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(Indexer::class, as: 'indexer'),
        DataFixture(Customer::class, ['email' => 'customer@example.com'], as: 'customer'),
        DataFixture(
            CustomerCart::class,
            [
                'customer_id' => '$customer.id$',
                'reserved_order_id' => 'test_quote'
            ],
            'cart'
        ),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(
            ProductStockFixture::class,
            [
                'prod_id' => '$product.id$',
                'is_in_stock' => 0,
                'prod_qty' => 0
            ],
            'prodStock'
        ),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    public function testPlaceOrderWithOutOfStockProduct()
    {
        $maskedQuoteId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $query = $this->getQuery($maskedQuoteId);

        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());
        self::assertEquals(1, count($response['placeOrder']['errors']));
        self::assertEquals('UNABLE_TO_PLACE_ORDER', $response['placeOrder']['errors'][0]['code']);
        self::assertEquals(
            'Some of the products are out of stock.',
            $response['placeOrder']['errors'][0]['message']
        );
    }

    #[
        Config('carriers/flatrate/active', '1', 'store', 'default'),
        Config('carriers/tablerate/active', '1', 'store', 'default'),
        Config('carriers/freeshipping/active', '1', 'store', 'default'),
        Config('cataloginventory/options/enable_inventory_check', 0),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(Indexer::class, as: 'indexer'),
        DataFixture(Customer::class, ['email' => 'customer@example.com'], as: 'customer'),
        DataFixture(
            CustomerCart::class,
            [
                'customer_id' => '$customer.id$',
                'reserved_order_id' => 'test_quote'
            ],
            'cart'
        ),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(
            ProductStockFixture::class,
            [
                'prod_id' => '$product.id$',
                'is_in_stock' => 0,
                'prod_qty' => 0
            ],
            'prodStock'
        ),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    public function testPlaceOrderWithOutOfStockProductWithDisabledInventoryCheck()
    {
        $maskedQuoteId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $query = $this->getQuery($maskedQuoteId);

        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());
        self::assertEquals(1, count($response['placeOrder']['errors']));
        self::assertEquals('UNABLE_TO_PLACE_ORDER', $response['placeOrder']['errors'][0]['code']);
        self::assertEquals(
            'Enter a valid payment method and try again.',
            $response['placeOrder']['errors'][0]['message']
        );
    }

    #[
        Config('carriers/flatrate/active', '1', 'store', 'default'),
        Config('carriers/tablerate/active', '1', 'store', 'default'),
        Config('carriers/freeshipping/active', '1', 'store', 'default'),
        Config('payment/banktransfer/active', '1', 'store', 'default'),
        Config('payment/cashondelivery/active', '1', 'store', 'default'),
        Config('payment/checkmo/active', '1', 'store', 'default'),
        Config('payment/purchaseorder/active', '1', 'store', 'default'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(Indexer::class, as: 'indexer'),
        DataFixture(Customer::class, ['email' => 'customer@example.com'], as: 'customer'),
        DataFixture(GuestCartFixture::class, ['reserved_order_id' => 'test_quote'], as: 'cart'),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
    ]
    public function testPlaceOrderOfGuestCart()
    {
        $cart = DataFixtureStorageManager::getStorage()->get('cart');
        $maskedQuoteId = $this->quoteIdToMaskedQuoteIdInterface->execute((int) $cart->getId());
        $query = $this->getQuery($maskedQuoteId);

        self::expectExceptionMessageMatches('/The current user cannot perform operations on cart*/');
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    #[
        Config('carriers/flatrate/active', '1', 'store', 'default'),
        Config('carriers/tablerate/active', '1', 'store', 'default'),
        Config('carriers/freeshipping/active', '1', 'store', 'default'),
        Config('payment/banktransfer/active', '1', 'store', 'default'),
        Config('payment/cashondelivery/active', '1', 'store', 'default'),
        Config('payment/checkmo/active', '1', 'store', 'default'),
        Config('payment/purchaseorder/active', '1', 'store', 'default'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(Indexer::class, as: 'indexer'),
        DataFixture(Customer::class, ['email' => 'customer@example.com'], as: 'customer'),
        DataFixture(Customer::class, ['email' => 'customer3@search.example.com'], as: 'customer2'),
        DataFixture(
            CustomerCart::class,
            [
                'customer_id' => '$customer.id$',
                'reserved_order_id' => 'test_quote'
            ],
            'cart'
        ),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    public function testPlaceOrderOfAnotherCustomerCart()
    {
        $maskedQuoteId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $query = $this->getQuery($maskedQuoteId);

        self::expectExceptionMessageMatches('/The current user cannot perform operations on cart*/');
        $this->graphQlMutation($query, [], '', $this->getHeaderMap('customer3@search.example.com'));
    }

    /**
     * @param string $maskedQuoteId
     * @return string
     */
    private function getQuery(string $maskedQuoteId): string
    {
        return <<<QUERY
mutation {
  placeOrder(input: {cart_id: "{$maskedQuoteId}"}) {
    order {
      order_number
    }
    errors {
      message
      code
    }
  }
}
QUERY;
    }

    /**
     * @param string $username
     * @param string $password
     * @return array
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    private function getHeaderMap(string $username = 'customer@example.com', string $password = 'password'): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($username, $password);
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];
        return $headerMap;
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);

        $orderCollection = $this->orderCollectionFactory->create();
        foreach ($orderCollection as $order) {
            $this->orderRepository->delete($order);
        }
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', false);

        parent::tearDown();
    }
}
