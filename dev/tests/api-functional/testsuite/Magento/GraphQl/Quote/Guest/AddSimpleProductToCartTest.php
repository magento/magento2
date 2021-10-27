<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Guest;

use Exception;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQl\ResponseContainsErrorsException;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Add simple product to cart testcases
 */
class AddSimpleProductToCartTest extends GraphQlAbstract
{
    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     */
    public function testAddSimpleProductToCart()
    {
        $sku = 'simple_product';
        $quantity = 2;
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = $this->getQuery($maskedQuoteId, $sku, $quantity);
        $response = $this->graphQlMutation($query);
        self::assertArrayHasKey('cart', $response['addSimpleProductsToCart']);

        self::assertArrayHasKey('shipping_addresses', $response['addSimpleProductsToCart']['cart']);
        self::assertEmpty($response['addSimpleProductsToCart']['cart']['shipping_addresses']);
        self::assertEquals($quantity, $response['addSimpleProductsToCart']['cart']['items'][0]['quantity']);
        self::assertEquals($sku, $response['addSimpleProductsToCart']['cart']['items'][0]['product']['sku']);
        self::assertArrayHasKey('prices', $response['addSimpleProductsToCart']['cart']['items'][0]);
        self::assertArrayHasKey('id', $response['addSimpleProductsToCart']['cart']);
        self::assertEquals($maskedQuoteId, $response['addSimpleProductsToCart']['cart']['id']);

        self::assertArrayHasKey('price', $response['addSimpleProductsToCart']['cart']['items'][0]['prices']);
        $price = $response['addSimpleProductsToCart']['cart']['items'][0]['prices']['price'];
        self::assertArrayHasKey('value', $price);
        self::assertEquals(10, $price['value']);
        self::assertArrayHasKey('currency', $price);
        self::assertEquals('USD', $price['currency']);

        self::assertArrayHasKey('row_total', $response['addSimpleProductsToCart']['cart']['items'][0]['prices']);
        $rowTotal = $response['addSimpleProductsToCart']['cart']['items'][0]['prices']['row_total'];
        self::assertArrayHasKey('value', $rowTotal);
        self::assertEquals(20, $rowTotal['value']);
        self::assertArrayHasKey('currency', $rowTotal);
        self::assertEquals('USD', $rowTotal['currency']);

        self::assertArrayHasKey(
            'row_total_including_tax',
            $response['addSimpleProductsToCart']['cart']['items'][0]['prices']
        );
        $rowTotalIncludingTax =
            $response['addSimpleProductsToCart']['cart']['items'][0]['prices']['row_total_including_tax'];
        self::assertArrayHasKey('value', $rowTotalIncludingTax);
        self::assertEquals(20, $rowTotalIncludingTax['value']);
        self::assertArrayHasKey('currency', $rowTotalIncludingTax);
        self::assertEquals('USD', $rowTotalIncludingTax['currency']);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/Store/_files/second_store.php
     */
    public function testAddSimpleProductWithDifferentStoreHeader()
    {
        $sku = 'simple_product';
        $quantity = 2;
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $headerMap = ['Store' => 'fixture_second_store'];
        $query = $this->getQuery($maskedQuoteId, $sku, $quantity);
        $response = $this->graphQlMutation($query, [], '', $headerMap);
        self::assertArrayHasKey('cart', $response['addSimpleProductsToCart']);

        self::assertArrayHasKey('shipping_addresses', $response['addSimpleProductsToCart']['cart']);
        self::assertEmpty($response['addSimpleProductsToCart']['cart']['shipping_addresses']);
        self::assertEquals($quantity, $response['addSimpleProductsToCart']['cart']['items'][0]['quantity']);
        self::assertEquals($sku, $response['addSimpleProductsToCart']['cart']['items'][0]['product']['sku']);
        self::assertArrayHasKey('prices', $response['addSimpleProductsToCart']['cart']['items'][0]);
        self::assertArrayHasKey('id', $response['addSimpleProductsToCart']['cart']);
        self::assertEquals($maskedQuoteId, $response['addSimpleProductsToCart']['cart']['id']);

        self::assertArrayHasKey('price', $response['addSimpleProductsToCart']['cart']['items'][0]['prices']);
        $price = $response['addSimpleProductsToCart']['cart']['items'][0]['prices']['price'];
        self::assertArrayHasKey('value', $price);
        self::assertEquals(10, $price['value']);
        self::assertArrayHasKey('currency', $price);
        self::assertEquals('USD', $price['currency']);

        self::assertArrayHasKey('row_total', $response['addSimpleProductsToCart']['cart']['items'][0]['prices']);
        $rowTotal = $response['addSimpleProductsToCart']['cart']['items'][0]['prices']['row_total'];
        self::assertArrayHasKey('value', $rowTotal);
        self::assertEquals(20, $rowTotal['value']);
        self::assertArrayHasKey('currency', $rowTotal);
        self::assertEquals('USD', $rowTotal['currency']);

        self::assertArrayHasKey(
            'row_total_including_tax',
            $response['addSimpleProductsToCart']['cart']['items'][0]['prices']
        );
        $rowTotalIncludingTax =
            $response['addSimpleProductsToCart']['cart']['items'][0]['prices']['row_total_including_tax'];
        self::assertArrayHasKey('value', $rowTotalIncludingTax);
        self::assertEquals(20, $rowTotalIncludingTax['value']);
        self::assertArrayHasKey('currency', $rowTotalIncludingTax);
        self::assertEquals('USD', $rowTotalIncludingTax['currency']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_with_image_no_options.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     */
    public function testAddProductToCartWithImage()
    {
        $sku = 'simple-2';
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = <<<QUERY
mutation {
  addSimpleProductsToCart(input: {
    cart_id: "$maskedQuoteId",
    cart_items: [{data: {sku: "$sku", quantity: 1}}]
  }) {
    cart {
      items {
        id
        prices{
          price {
            value
          }
        }
        quantity
        product {
          sku
          name
          image {
            label
            url
          }
        }
      }
    }
  }
}
QUERY;

        $response = $this->graphQlMutation($query);
        $this->assertArrayHasKey('cart', $response['addSimpleProductsToCart']);
        $this->assertCount(1, $response['addSimpleProductsToCart']['cart']['items']);
        $cartItem = $response['addSimpleProductsToCart']['cart']['items'][0];
        $this->assertEquals('11', $cartItem['prices']['price']['value']);
        $this->assertEquals($sku, $cartItem['product']['sku']);
        $expectedImageRegex = '/^https?:\/\/.+magento_image(_[0-9]+)?.jpg$/';
        $this->assertMatchesRegularExpression($expectedImageRegex, $cartItem['product']['image']['url']);
    }

    /**
     * Add disabled product to cart
     *
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_products.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @return void
     */
    public function testAddDisabledProductToCart(): void
    {
        $sku = 'simple3';
        $quantity = 2;

        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId, $sku, $quantity);

        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage(
            'GraphQL response contains errors: Could not find a product with SKU "' . $sku . '"'
        );

        $this->graphQlMutation($query);
    }

    /**
     * Add out of stock product to cart
     *
     * @magentoConfigFixture cataloginventory/options/enable_inventory_check 1
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_products.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/set_simple_product_out_of_stock.php
     * @return void
     * @throws NoSuchEntityException
     */
    public function testAddOutOfStockProductToCart(): void
    {
        $sku = 'simple1';
        $quantity = 1;

        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId, $sku, $quantity);

        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage(
            'Some of the products are out of stock.'
        );

        $this->graphQlMutation($query);
    }

    /**
     * Add out of stock product to cart with disabled quote item check
     *
     * @magentoConfigFixture cataloginventory/options/enable_inventory_check 0
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_products.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/set_simple_product_out_of_stock.php
     * @return void
     * @throws NoSuchEntityException
     */
    public function testAddOutOfStockProductToCartWithDisabledInventoryCheck(): void
    {
        $sku = 'simple1';
        $quantity = 1;

        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId, $sku, $quantity);
        $response = $this->graphQlMutation($query);

        $this->assertArrayHasKey('cart', $response['addSimpleProductsToCart']);
        $this->assertCount(2, $response['addSimpleProductsToCart']['cart']['items']);
        $cartItems = $response['addSimpleProductsToCart']['cart']['items'];
        $this->assertEquals(2, $cartItems[0]['quantity']);
        $this->assertEquals(1, $cartItems[1]['quantity']);
    }

    /**
     * Add out of stock simple product to cart with disabled quote item check
     *
     * @magentoConfigFixture cataloginventory/options/enable_inventory_check 1
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/set_simple_product_out_of_stock.php
     * @return void
     * @throws NoSuchEntityException
     */
    public function testAddOutOfStockSimpleProductToCart(): void
    {
        $sku = 'simple_product';
        $quantity = 1;

        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId, $sku, $quantity);

        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage(
            'Could not add the product with SKU ' . $sku . ' to the shopping cart: ' .
            'Product that you are trying to add is not available.'
        );

        $this->graphQlMutation($query);
    }

    /**
     * Add out of stock simple product to cart with disabled quote item check
     *
     * @magentoConfigFixture cataloginventory/options/enable_inventory_check 0
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/set_simple_product_out_of_stock.php
     * @return void
     * @throws NoSuchEntityException
     */
    public function testAddOutOfStockSimpleProductToCartWithDisabledInventoryCheck(): void
    {
        $sku = 'simple_product';
        $quantity = 1;

        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId, $sku, $quantity);

        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage(
            'Could not add the product with SKU ' . $sku . ' to the shopping cart: ' .
            'Product that you are trying to add is not available.'
        );

        $this->graphQlMutation($query);
    }

    /**
     */
    public function testAddSimpleProductToCartIfCartIdIsEmpty()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Required parameter "cart_id" is missing');

        $query = <<<QUERY
mutation {
  addSimpleProductsToCart(
    input: {
      cart_id: "",
      cart_items: []
    }
  ) {
    cart {
      items {
        id
      }
    }
  }
}
QUERY;

        $this->graphQlMutation($query);
    }

    /**
     */
    public function testAddSimpleProductToCartIfCartItemsAreEmpty()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Required parameter "cart_items" is missing');

        $query = <<<QUERY
mutation {
  addSimpleProductsToCart(
    input: {
      cart_id: "cart_id",
      cart_items: []
    }
  ) {
    cart {
      items {
        id
      }
    }
  }
}
QUERY;

        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     *
     */
    public function testAddProductToNonExistentCart()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Could not find a cart with ID "non_existent_masked_id"');

        $sku = 'simple_product';
        $quantity = 1;
        $maskedQuoteId = 'non_existent_masked_id';

        $query = $this->getQuery($maskedQuoteId, $sku, $quantity);
        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     *
     */
    public function testNonExistentProductToCart()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Could not find a product with SKU "simple_product"');

        $sku = 'simple_product';
        $quantity = 1;
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = $this->getQuery($maskedQuoteId, $sku, $quantity);
        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     */
    public function testAddSimpleProductToCustomerCart()
    {
        $sku = 'simple_product';
        $quantity = 2;
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId, $sku, $quantity);

        $this->expectExceptionMessage(
            "The current user cannot perform operations on cart \"$maskedQuoteId\""
        );

        $this->graphQlMutation($query);
    }

    /**
     * @param string $maskedQuoteId
     * @param string $sku
     * @param float $quantity
     * @return string
     */
    private function getQuery(string $maskedQuoteId, string $sku, float $quantity): string
    {
        return <<<QUERY
mutation {
  addSimpleProductsToCart(
    input: {
      cart_id: "{$maskedQuoteId}"
      cart_items: [
        {
          data: {
            quantity: $quantity
            sku: "$sku"
          }
        }
      ]
    }
  ) {
    cart {
    id
      items {
        quantity
        product {
          sku
        }
        prices {
          price {
           value
           currency
          }
          row_total {
           value
           currency
          }
          row_total_including_tax {
           value
           currency
          }
        }
      }
      shipping_addresses {
        firstname
        lastname
        company
        street
        city
        postcode
        telephone
        country {
          code
          label
        }
        __typename
      }
    }
  }
}
QUERY;
    }
}
