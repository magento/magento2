<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\QuoteCommerceGraphQl\Customer;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\GraphQl\GetCustomerAuthenticationHeader;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Quote\Test\Fixture\MakeCartInactive as MakeCartInactiveFixture;
use Magento\Quote\Test\Fixture\QuoteIdMask as QuoteIdMaskFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test coverage for clear customer cart
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ClearCartTest extends GraphQlAbstract
{
    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->fixtures = $objectManager->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * Test clear cart items
     *
     * @return void
     * @throws AuthenticationException
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    #[
        DataFixture(ProductFixture::class, as: 'p1'),
        DataFixture(ProductFixture::class, as: 'p2'),
        DataFixture(
            Customer::class,
            [
                'email' => 'customer@example.com',
                'password' => 'password'
            ],
            'customer'
        ),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'cart'),
        DataFixture(QuoteIdMaskFixture::class, ['cart_id' => '$cart.id$'], as: 'mask'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$p1.id$', 'qty' => 2]),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$p2.id$', 'qty' => 2]),
    ]
    public function testClearCart(): void
    {
        $maskedQuoteId = $this->fixtures->get('mask')->getMaskedId();
        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlMutation($query, [], '', $this->getHeaders());
        $this->assertArrayHasKey('clearCart', $response);
        $this->assertEmpty($response['clearCart']['cart']['items']);
        $this->assertEquals(null, $response['clearCart']['errors']);
    }

    /**
     * Test exception if masked cart id is missing
     *
     * @return void
     * @throws AuthenticationException
     * @throws LocalizedException
     */
    #[
        DataFixture(
            Customer::class,
            [
                'email' => 'customer@example.com',
                'password' => 'password'
            ],
            'customer'
        ),
    ]
    public function testClearCartWithoutId(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Required parameter "uid" is missing.');
        $maskedQuoteId = '';
        $query = $this->getQuery($maskedQuoteId);
        $this->graphQlMutation($query, [], '', $this->getHeaders());
    }

    /**
     * Test clear cart items for wrong cart id
     *
     * @return void
     * @throws \Exception
     */
    #[
        DataFixture(
            Customer::class,
            [
                'email' => 'customer@example.com',
                'password' => 'password'
            ],
            'customer'
        ),
    ]
    public function testClearCartWithWrongCartId(): void
    {
        $maskedQuoteId = "abc12345abc";
        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlMutation($query, [], '', $this->getHeaders());
        $this->assertEquals("NOT_FOUND", $response['clearCart']['errors'][0]['type']);
        $this->assertEquals(null, $response['clearCart']['cart']);
    }

    /**
     * Test clear cart items for inactive cart id
     *
     * @return void
     * @throws \Exception
     */
    #[
        DataFixture(
            Customer::class,
            [
                'email' => 'customer@example.com',
                'password' => 'password'
            ],
            'customer'
        ),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'cart'),
        DataFixture(QuoteIdMaskFixture::class, ['cart_id' => '$cart.id$'], as: 'mask'),
        DataFixture(MakeCartInactiveFixture::class, ['cart_id' => '$cart.id$'], as: 'inactiveCart'),
    ]
    public function testClearCartWithInactiveCartId()
    {
        $maskedQuoteId = $this->fixtures->get('mask')->getMaskedId();
        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlMutation($query, [], '', $this->getHeaders());
        $this->assertEquals("INACTIVE", $response['clearCart']['errors'][0]['type']);
        $this->assertEquals(null, $response['clearCart']['cart']);
    }

    /**
     * Test clear cart for unathorised cart id
     *
     * @return void
     * @throws \Exception
     */
    #[
        DataFixture(
            Customer::class,
            [
                'email' => 'customer@example.com',
                'password' => 'password'
            ],
            'customer'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(QuoteIdMaskFixture::class, ['cart_id' => '$cart.id$'], as: 'mask'),
    ]
    public function testClearCartWithUnathorisedCartId(): void
    {
        $maskedQuoteId = $this->fixtures->get('mask')->getMaskedId();
        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlMutation($query, [], '', $this->getHeaders());
        $this->assertEquals("UNAUTHORISED", $response['clearCart']['errors'][0]['type']);
        $this->assertEquals(null, $response['clearCart']['cart']);
    }

    /**
     * Return headers for graphql
     *
     * @return string[]
     * @throws AuthenticationException|LocalizedException
     */
    private function getHeaders(): array
    {
        /** @var CustomerInterface $customer */
        $customer = DataFixtureStorageManager::getStorage()->get('customer');
        return Bootstrap::getObjectManager()->get(GetCustomerAuthenticationHeader::class)
            ->execute($customer->getEmail());
    }

    /**
     * Returns GraphQl mutation string
     *
     * @param string $cartId
     *
     * @return string
     */
    private function getQuery(
        string $cartId
    ): string {
        return <<<MUTATION
mutation {
  clearCart(
    input:{
    uid: "{$cartId}"
    }
  ) {
    cart {
      id
      items {
        id
        product {
          sku
          stock_status
        }
        quantity
      }
    }
    errors {
    type
    message
    }
  }
}
MUTATION;
    }
}
