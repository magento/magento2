<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Guest;

use Exception;
use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for getting cart information
 */
class GetCartTest extends GraphQlAbstract
{
    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_virtual.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_virtual_product.php
     */
    public function testGetCart()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId);

        $response = $this->graphQlQuery($query);

        self::assertArrayHasKey('cart', $response);
        self::assertArrayHasKey('items', $response['cart']);
        self::assertArrayHasKey('id', $response['cart']);
        self::assertEquals($maskedQuoteId, $response['cart']['id']);
        self::assertCount(2, $response['cart']['items']);

        self::assertNotEmpty($response['cart']['items'][0]['id']);
        self::assertEquals(2, $response['cart']['items'][0]['quantity']);
        self::assertEquals('simple_product', $response['cart']['items'][0]['product']['sku']);

        self::assertNotEmpty($response['cart']['items'][1]['id']);
        self::assertEquals(2, $response['cart']['items'][1]['quantity']);
        self::assertEquals('virtual-product', $response['cart']['items'][1]['product']['sku']);
    }

    /**
     * _security
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     */
    public function testGetCustomerCart()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId);

        $this->expectExceptionMessage(
            "The current user cannot perform operations on cart \"{$maskedQuoteId}\""
        );
        $this->graphQlQuery($query);
    }

    /**
     */
    public function testGetCartIfCartIdIsEmpty()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Required parameter "cart_id" is missing');

        $maskedQuoteId = '';
        $query = $this->getQuery($maskedQuoteId);

        $this->graphQlQuery($query);
    }

    /**
     */
    public function testGetCartIfCartIdIsMissed()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Field "cart" argument "cart_id" of type "String!" is required but not provided.');

        $query = <<<QUERY
{
  cart {
    email
  }
}
QUERY;

        $this->graphQlQuery($query);
    }

    /**
     */
    public function testGetNonExistentCart()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Could not find a cart with ID "non_existent_masked_id"');

        $maskedQuoteId = 'non_existent_masked_id';
        $query = $this->getQuery($maskedQuoteId);

        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/make_cart_inactive.php
     *
     */
    public function testGetInactiveCart()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The cart isn\'t active.');

        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId);

        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote_guest_not_default_store.php
     */
    public function testGetCartWithNotDefaultStore()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1_not_default_store_guest');
        $query = $this->getQuery($maskedQuoteId);

        $headerMap = ['Store' => 'fixture_second_store'];
        $response = $this->graphQlQuery($query, [], '', $headerMap);

        self::assertArrayHasKey('cart', $response);
        self::assertArrayHasKey('items', $response['cart']);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     * @magentoApiDataFixture Magento/Store/_files/second_store.php
     *
     */
    public function testGetCartWithWrongStore()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Wrong store code specified for cart');

        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');
        $query = $this->getQuery($maskedQuoteId);

        $headerMap = ['Store' => 'fixture_second_store'];
        $this->graphQlQuery($query, [], '', $headerMap);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote_guest_not_default_store.php
     *
     */
    public function testGetCartWithNotExistingStore()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Requested store is not found');

        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1_not_default_store_guest');

        $headerMap['Store'] = 'not_existing_store';
        $query = $this->getQuery($maskedQuoteId);

        $this->graphQlQuery($query, [], '', $headerMap);
    }

    /**
     * @param string $maskedQuoteId
     * @return string
     */
    private function getQuery(string $maskedQuoteId): string
    {
        return <<<QUERY
{
  cart(cart_id: "{$maskedQuoteId}") {
    id
    items {
      id
      quantity
      product {
        sku
      }
    }
  }
}
QUERY;
    }
}
