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
use Magento\Quote\Test\Fixture\QuoteIdMask;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\SalesGraphQl\Model\Order\Token;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for guestOrderByToken query
 */
class GuestOrderByTokenTest extends GraphQlAbstract
{
    private const GUEST_ORDER_BY_TOKEN = <<<QUERY
{
  guestOrderByToken(input: {
      token: "%token"
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

    private const PLACE_ORDER = <<<QUERY
mutation {
  placeOrder(input: {
      cart_id: "%cart_id"
  }) {
    orderV2 {
      number
      email
      billing_address {
        firstname
        lastname
      }
      token
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
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$'], 'email'),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    public function testGuestOrder(): void
    {
        $maskedQuoteId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $placeOrderQuery = strtr(
            self::PLACE_ORDER,
            [
                '%cart_id' => $maskedQuoteId
            ]
        );
        $placeOrderResponse = $this->graphQlMutation($placeOrderQuery);

        $this->assertNotEmpty($placeOrderResponse['placeOrder']['orderV2']['number']);
        $this->assertNotEmpty($placeOrderResponse['placeOrder']['orderV2']['token']);
        $this->assertNotEmpty($placeOrderResponse['placeOrder']['orderV2']['email']);
        $this->assertNotEmpty($placeOrderResponse['placeOrder']['orderV2']['billing_address']['firstname']);
        $this->assertNotEmpty($placeOrderResponse['placeOrder']['orderV2']['billing_address']['lastname']);

        $query = strtr(
            self::GUEST_ORDER_BY_TOKEN,
            [
                '%token' => $placeOrderResponse['placeOrder']['orderV2']['token'],
            ]
        );
        $response = $this->graphQlQuery($query);
        self::assertEquals(
            [
                'guestOrderByToken' => [
                    'number' => $placeOrderResponse['placeOrder']['orderV2']['number'],
                    'email' => $placeOrderResponse['placeOrder']['orderV2']['email'],
                    'billing_address' => [
                        'firstname' => $placeOrderResponse['placeOrder']['orderV2']['billing_address']['firstname'],
                        'lastname' => $placeOrderResponse['placeOrder']['orderV2']['billing_address']['lastname']
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
            self::GUEST_ORDER_BY_TOKEN,
            [
                '%token' => Bootstrap::getObjectManager()->get(Token::class)->encrypt(
                    $order->getIncrementId(),
                    $order->getBillingAddress()->getEmail(),
                    $order->getBillingAddress()->getLastname()
                )
            ]
        );
        $this->graphQlQuery($query);
    }

    public function testGuestOrderIncorrectToken(): void
    {
        $this->expectExceptionMessage('We couldn\'t locate an order with the information provided.');
        $query = strtr(
            self::GUEST_ORDER_BY_TOKEN,
            [
                '%token' => 'incorrect'
            ]
        );
        $this->graphQlQuery($query);
    }
}
