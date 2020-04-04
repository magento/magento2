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

    protected function setUp()
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
     * @expectedException Exception
     * @expectedExceptionMessage Required parameter "cart_id" is missing
     */
    public function testGetCartIfCartIdIsEmpty()
    {
        $maskedQuoteId = '';
        $query = $this->getQuery($maskedQuoteId);

        $this->graphQlQuery($query);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Field "cart" argument "cart_id" of type "String!" is required but not provided.
     */
    public function testGetCartIfCartIdIsMissed()
    {
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
     * @expectedException Exception
     * @expectedExceptionMessage Could not find a cart with ID "non_existent_masked_id"
     */
    public function testGetNonExistentCart()
    {
        $maskedQuoteId = 'non_existent_masked_id';
        $query = $this->getQuery($maskedQuoteId);

        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/make_cart_inactive.php
     *
     * @expectedException Exception
     * @expectedExceptionMessage Current user does not have an active cart.
     */
    public function testGetInactiveCart()
    {
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
     * @expectedException Exception
     * @expectedExceptionMessage Wrong store code specified for cart
     */
    public function testGetCartWithWrongStore()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');
        $query = $this->getQuery($maskedQuoteId);

        $headerMap = ['Store' => 'fixture_second_store'];
        $this->graphQlQuery($query, [], '', $headerMap);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote_guest_not_default_store.php
     *
     * @expectedException Exception
     * @expectedExceptionMessage Requested store is not found
     */
    public function testGetCartWithNotExistingStore()
    {
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
