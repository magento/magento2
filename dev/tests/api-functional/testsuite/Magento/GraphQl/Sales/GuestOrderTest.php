<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Sales;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetGuestEmail as SetGuestEmailFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\Quote\Test\Fixture\GuestCart;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for guestOrder query
 */
class GuestOrderTest extends GraphQlAbstract
{
    private const GUEST_ORDER = <<<QUERY
{
  guestOrder(input: {
      number: "%number",
      email: "%email",
      lastname: "%lastname"
  }) {
    number
    email
    billing_address {
      firstname
      lastname
    }
  }
}
QUERY;

    #[
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCart::class, as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
    ]
    public function testGuestOrder(): void
    {
        /** @var OrderInterface $order */
        $order = DataFixtureStorageManager::getStorage()->get('order');
        $query = strtr(
            self::GUEST_ORDER,
            [
                '%number' => $order->getIncrementId(),
                '%email' => $order->getBillingAddress()->getEmail(),
                '%lastname' => $order->getBillingAddress()->getLastname(),
            ]
        );
        $response = $this->graphQlQuery($query);
        self::assertEquals(
            [
                'guestOrder' => [
                    'number' => $order->getIncrementId(),
                    'email' => $order->getBillingAddress()->getEmail(),
                    'billing_address' => [
                        'firstname' => $order->getBillingAddress()->getFirstname(),
                        'lastname' => $order->getBillingAddress()->getLastname()
                    ]
                ]
            ],
            $response
        );
    }

    #[
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
    ]
    public function testCustomerOrder(): void
    {
        $this->expectExceptionMessage('Please login to view the order.');
        /** @var OrderInterface $order */
        $order = DataFixtureStorageManager::getStorage()->get('order');
        $query = strtr(
            self::GUEST_ORDER,
            [
                '%number' => $order->getIncrementId(),
                '%email' => $order->getBillingAddress()->getEmail(),
                '%lastname' => $order->getBillingAddress()->getLastname(),
            ]
        );
        $this->graphQlQuery($query);
    }

    #[
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCart::class, as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
    ]
    public function testGuestOrderIncorrectEmail(): void
    {
        $this->expectExceptionMessage('We couldn\'t locate an order with the information provided.');
        /** @var OrderInterface $order */
        $order = DataFixtureStorageManager::getStorage()->get('order');
        $query = strtr(
            self::GUEST_ORDER,
            [
                '%number' => $order->getIncrementId(),
                '%email' => 'incorrect' . $order->getBillingAddress()->getEmail(),
                '%lastname' => $order->getBillingAddress()->getLastname(),
            ]
        );
        $this->graphQlQuery($query);
    }
}
