<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Guest;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Catalog\Test\Fixture\ProductStock as ProductStockFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetGuestEmail as SetGuestEmailFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\GiftMessage\Test\Fixture\GiftMessage;
use Magento\Indexer\Test\Fixture\Indexer;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Quote\Test\Fixture\QuoteIdMask;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for placing an order for guest
 */
class PlaceOrderTest extends GraphQlAbstract
{
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
     * @var OrderFactory
     */
    private $orderFactory;

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
        $this->orderFactory = $objectManager->get(OrderFactory::class);
        $this->orderCollectionFactory = $objectManager->get(CollectionFactory::class);
        $this->orderRepository = $objectManager->get(OrderRepositoryInterface::class);
        $this->registry = $objectManager->get(Registry::class);
        /** @var ScopeConfigInterface $scopeConfig */
        $scopeConfig = $objectManager->get(ScopeConfigInterface::class);
        $scopeConfig->clean();
    }

    #[
        Config('carriers/flatrate/active', '1', 'store', 'default'),
        Config('carriers/tablerate/active', '1', 'store', 'default'),
        Config('carriers/freeshipping/active', '1', 'store', 'default'),
        Config('payment/banktransfer/active', '1', 'store', 'default'),
        Config('payment/cashondelivery/active', '1', 'store', 'default'),
        Config('payment/checkmo/active', '1', 'store', 'default'),
        Config('payment/purchaseorder/active', '1', 'store', 'default'),
        Config('customer/create_account/auto_group_assign', '0', 'store', 'default'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(Indexer::class, as: 'indexer'),
        DataFixture(GuestCartFixture::class, ['reserved_order_id' => 'test_quote'], as: 'cart'),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
    ]
    public function testPlaceOrder()
    {
        $reservedOrderId = 'test_quote';
        $cart = DataFixtureStorageManager::getStorage()->get('cart');
        $maskedQuoteId = $this->quoteIdToMaskedQuoteIdInterface->execute((int) $cart->getId());

        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlMutation($query);

        self::assertArrayHasKey('placeOrder', $response);
        self::assertArrayHasKey('order_number', $response['placeOrder']['order']);
        self::assertArrayHasKey('number', $response['placeOrder']['orderV2']);
        self::assertEquals($reservedOrderId, $response['placeOrder']['order']['order_number']);
        self::assertEquals($reservedOrderId, $response['placeOrder']['orderV2']['number']);
        self::assertEmpty(count($response['placeOrder']['errors']));
        $orderIncrementId = $response['placeOrder']['order']['order_number'];
        $order = $this->orderFactory->create();
        $order->loadByIncrementId($orderIncrementId);
        $this->assertNotEmpty($order->getEmailSent());
    }

    #[
        Config('carriers/flatrate/active', '1', 'store', 'default'),
        Config('carriers/tablerate/active', '1', 'store', 'default'),
        Config('carriers/freeshipping/active', '1', 'store', 'default'),
        Config('payment/banktransfer/active', '1', 'store', 'default'),
        Config('payment/cashondelivery/active', '1', 'store', 'default'),
        Config('payment/checkmo/active', '1', 'store', 'default'),
        Config('payment/purchaseorder/active', '1', 'store', 'default'),
        Config('customer/create_account/auto_group_assign', '1', 'store', 'default'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(Indexer::class, as: 'indexer'),
        DataFixture(GuestCartFixture::class, ['reserved_order_id' => 'test_quote'], as: 'cart'),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
    ]
    public function testPlaceOrderWithAutoGroup()
    {
        $reservedOrderId = 'test_quote';
        $cart = DataFixtureStorageManager::getStorage()->get('cart');
        $maskedQuoteId = $this->quoteIdToMaskedQuoteIdInterface->execute((int) $cart->getId());

        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlMutation($query);

        self::assertArrayHasKey('placeOrder', $response);
        self::assertArrayHasKey('order_number', $response['placeOrder']['order']);
        self::assertArrayHasKey('number', $response['placeOrder']['orderV2']);
        self::assertEquals($reservedOrderId, $response['placeOrder']['order']['order_number']);
        self::assertEquals($reservedOrderId, $response['placeOrder']['orderV2']['number']);
        self::assertEmpty(count($response['placeOrder']['errors']));
        $orderIncrementId = $response['placeOrder']['order']['order_number'];
        $order = $this->orderFactory->create();
        $order->loadByIncrementId($orderIncrementId);
        $this->assertNotEmpty($order->getEmailSent());
    }

    #[
        Config('customer/create_account/auto_group_assign', '0', 'store', 'default'),
    ]
    public function testPlaceOrderIfCartIdIsEmpty()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Required parameter "cart_id" is missing');

        $maskedQuoteId = '';
        $query = $this->getQuery($maskedQuoteId);

        $this->graphQlMutation($query);
    }

    #[
        Config('carriers/flatrate/active', '1', 'store', 'default'),
        Config('carriers/tablerate/active', '1', 'store', 'default'),
        Config('carriers/freeshipping/active', '1', 'store', 'default'),
        Config('payment/banktransfer/active', '1', 'store', 'default'),
        Config('payment/cashondelivery/active', '1', 'store', 'default'),
        Config('payment/checkmo/active', '1', 'store', 'default'),
        Config('payment/purchaseorder/active', '1', 'store', 'default'),
        Config('customer/create_account/auto_group_assign', '0', 'store', 'default'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(Indexer::class, as: 'indexer'),
        DataFixture(GuestCartFixture::class, ['reserved_order_id' => 'test_quote'], as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
    ]
    public function testPlaceOrderWithNoEmail()
    {
        $cart = DataFixtureStorageManager::getStorage()->get('cart');
        $maskedQuoteId = $this->quoteIdToMaskedQuoteIdInterface->execute((int) $cart->getId());
        $query = $this->getQuery($maskedQuoteId);

        $response = $this->graphQlMutation($query);
        self::assertEquals(1, count($response['placeOrder']['errors']));
        self::assertEquals('GUEST_EMAIL_MISSING', $response['placeOrder']['errors'][0]['code']);
        self::assertEquals(
            'Guest email for cart is missing.',
            $response['placeOrder']['errors'][0]['message']
        );
    }

    #[
        DataFixture(GuestCartFixture::class, ['reserved_order_id' => 'test_quote'], as: 'cart'),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
    ]
    public function testPlaceOrderWithNoItemsInCart()
    {
        $cart = DataFixtureStorageManager::getStorage()->get('cart');
        $maskedQuoteId = $this->quoteIdToMaskedQuoteIdInterface->execute((int) $cart->getId());
        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlMutation($query);
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
        DataFixture(GuestCartFixture::class, ['reserved_order_id' => 'test_quote'], as: 'cart'),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
    ]
    public function testPlaceOrderWithNoShippingAddress()
    {
        $cart = DataFixtureStorageManager::getStorage()->get('cart');
        $maskedQuoteId = $this->quoteIdToMaskedQuoteIdInterface->execute((int) $cart->getId());
        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlMutation($query);
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
        DataFixture(GuestCartFixture::class, ['reserved_order_id' => 'test_quote'], as: 'cart'),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
    ]
    public function testPlaceOrderWithNoShippingMethod()
    {
        $cart = DataFixtureStorageManager::getStorage()->get('cart');
        $maskedQuoteId = $this->quoteIdToMaskedQuoteIdInterface->execute((int) $cart->getId());
        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlMutation($query);
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
        DataFixture(GuestCartFixture::class, ['reserved_order_id' => 'test_quote'], as: 'cart'),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
    ]
    public function testPlaceOrderWithNoBillingAddress()
    {
        $cart = DataFixtureStorageManager::getStorage()->get('cart');
        $maskedQuoteId = $this->quoteIdToMaskedQuoteIdInterface->execute((int) $cart->getId());
        $query = $this->getQuery($maskedQuoteId);

        $response = $this->graphQlMutation($query);
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
        DataFixture(GuestCartFixture::class, ['reserved_order_id' => 'test_quote'], as: 'cart'),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
    ]
    public function testPlaceOrderWithNoPaymentMethod()
    {
        $cart = DataFixtureStorageManager::getStorage()->get('cart');
        $maskedQuoteId = $this->quoteIdToMaskedQuoteIdInterface->execute((int) $cart->getId());
        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlMutation($query);
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
        DataFixture(GuestCartFixture::class, ['reserved_order_id' => 'test_quote'], as: 'cart'),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
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
    ]
    public function testPlaceOrderWithOutOfStockProduct()
    {
        $cart = DataFixtureStorageManager::getStorage()->get('cart');
        $maskedQuoteId = $this->quoteIdToMaskedQuoteIdInterface->execute((int) $cart->getId());
        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlMutation($query);
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
        DataFixture(GuestCartFixture::class, ['reserved_order_id' => 'test_quote'], as: 'cart'),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
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
        )
    ]
    public function testPlaceOrderWithOutOfStockProductWithDisabledInventoryCheck()
    {
        $cart = DataFixtureStorageManager::getStorage()->get('cart');
        $maskedQuoteId = $this->quoteIdToMaskedQuoteIdInterface->execute((int) $cart->getId());
        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlMutation($query);
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
        Config('customer/create_account/auto_group_assign', '0', 'store', 'default'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(Indexer::class, as: 'indexer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    public function testPlaceOrderOfCustomerCart()
    {
        $maskedQuoteId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $query = $this->getQuery($maskedQuoteId);

        self::expectExceptionMessageMatches('/The current user cannot perform operations on cart*/');
        $this->graphQlMutation($query);
    }

    #[
        Config('carriers/flatrate/active', '1', 'store', 'default'),
        Config('carriers/tablerate/active', '1', 'store', 'default'),
        Config('carriers/freeshipping/active', '1', 'store', 'default'),
        Config('payment/banktransfer/active', '1', 'store', 'default'),
        Config('payment/cashondelivery/active', '1', 'store', 'default'),
        Config('payment/checkmo/active', '1', 'store', 'default'),
        Config('payment/purchaseorder/active', '1', 'store', 'default'),
        Config('sales/gift_options/allow_order', 1),
        Config('customer/create_account/auto_group_assign', '1', 'store', 'default'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(Indexer::class, as: 'indexer'),
        DataFixture(GiftMessage::class, as: 'message'),
        DataFixture(
            GuestCartFixture::class,
            [
                'reserved_order_id' => 'test_quote',
                'message_id' => '$message.id$'
            ],
            'cart'
        ),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
    ]
    public function testPlaceOrderWithGiftMessage()
    {
        $reservedOrderId = 'test_quote';
        $cart = DataFixtureStorageManager::getStorage()->get('cart');
        $maskedQuoteId = $this->quoteIdToMaskedQuoteIdInterface->execute((int) $cart->getId());
        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlMutation($query);

        self::assertArrayHasKey('placeOrder', $response);
        self::assertArrayHasKey('order_number', $response['placeOrder']['order']);
        self::assertArrayHasKey('number', $response['placeOrder']['orderV2']);
        self::assertEquals($reservedOrderId, $response['placeOrder']['order']['order_number']);
        self::assertEquals($reservedOrderId, $response['placeOrder']['orderV2']['number']);
        $orderIncrementId = $response['placeOrder']['order']['order_number'];
        $order = $this->orderFactory->create();
        $order->loadByIncrementId($orderIncrementId);
        $this->assertNotEmpty($order->getGiftMessageId());
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
    orderV2 {
      number
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
