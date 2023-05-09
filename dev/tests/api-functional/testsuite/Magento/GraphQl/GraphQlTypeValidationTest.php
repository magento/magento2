<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class GraphQlTypeValidationTest extends GraphQlAbstract
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
    }

    /**
     *
     * Tests that field expecting an Int type ; but Float is provided
     *
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     */
    public function testIntegerExpectedWhenFloatProvided()
    {
        $query
            = <<<'QUERY'
query GetProductsQuery($pageSize: Int, $filterInput: ProductAttributeFilterInput, $currentPage:Int ) {
  products(
    filter: $filterInput
    pageSize: $pageSize
    currentPage: $currentPage

  ) {
    items {
      sku
      name
     id
    }
  }
}
QUERY;

        $variables = [

            'filterInput' => [
                'sku' => [
                    'eq' => 'simple_product',
                ],
            ],
            'pageSize' => 1,
            'currentPage' => 1.1
        ];
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Variable "$currentPage" got invalid value 1.1; ' .
            'Int cannot represent non-integer value: 1.1');
        $this->graphQlQuery($query, $variables);
    }

    /**
     * Tests that field expects an Float type ; but String is provided
     *
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     */
    public function testFloatExpectedWhenStringProvided()
    {
        $sku = 'simple_product';
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get($sku, false, null, true);
        $cartId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->addProductsToCart();
        $variables = [
            'cartId' => $cartId,
            'sku' => $sku,
            'quantity' => '1.9'
        ];
        $response = $this->graphQlMutation($query, $variables);
        $this->assertArrayNotHasKey('errors', $response);
        $this->assertArrayHasKey('addProductsToCart', $response);
        $this->assertCount(1, $response['addProductsToCart']['cart']['items']);
        $this->assertEquals($product->getSku(), $response['addProductsToCart']['cart']['items'][0]['product']['sku']);
        $this->assertEquals(1.9, $response['addProductsToCart']['cart']['items'][0]['quantity']);
    }

    /**
     *  Verify that query is resolved even when field expecting an Int is provided with String type data
     *
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testIntegerExpectedWhenStringProvided()
    {
        $cartId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $quantity = 5;
        $itemId = $this->getCartItemId($cartId);
        $query
            = <<<'MUTATION'
mutation updateItemQuantity($cartId: String!, $itemId: Int!, $quantity: Float!)
{
  updateCartItems(input:
    {cart_id: $cartId,
      cart_items:
      [
        {cart_item_id: $itemId,
        quantity: $quantity}]}
  )
  {
    cart
    {
      id
      items {id product {sku id } quantity}
    }
  }
}
MUTATION;

        // $itemId expects an integer type, but a string value is provided
        $variables = [
            'cartId' => $cartId,
            'itemId'=> "{$itemId}",
             'quantity'=> $quantity
        ];
        $response = $this->graphQlMutation($query, $variables);
        $this->assertArrayNotHasKey('errors', $response);
        $this->assertArrayHasKey('updateCartItems', $response);
        $this->assertCount(1, $response['updateCartItems']['cart']['items']);
        $this->assertEquals('simple_product', $response['updateCartItems']['cart']['items'][0]['product']['sku']);
        $this->assertEquals(5, $response['updateCartItems']['cart']['items'][0]['quantity']);
        $this->assertEquals($itemId, $response['updateCartItems']['cart']['items'][0]['id']);
    }

    /**
     * Verify that query doesn't return error when an integer is passed for a field where string is expected
     *
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product_with_numeric_sku.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     */
    public function testStringExpectedWhenFloatProvided()
    {
        $cartId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->addProductsToCart();

        // sku is expecting a string ; but an float  s given.
        // And quantity is expecting a float, but value is passed as string
        $variables = [
            'cartId' => $cartId,
            'sku' => 123.78,
            'quantity' => '5.60'
        ];
        $response = $this->graphQlMutation($query, $variables);
        $this->assertArrayNotHasKey('errors', $response);
        $this->assertArrayHasKey('addProductsToCart', $response);
        $this->assertCount(1, $response['addProductsToCart']['cart']['items']);
        $this->assertEquals('123.78', $response['addProductsToCart']['cart']['items'][0]['product']['sku']);
        $this->assertEquals(5.60, $response['addProductsToCart']['cart']['items'][0]['quantity']);
    }

    /**
     * Verify that query doesn't return error when an integer is passed for a field where string is expected
     *
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product_with_numeric_sku.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     */
    public function testStringExpectedWhenArrayProvided()
    {
        $cartId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->addProductsToCart();

        // sku is expecting a string ; but an array is passed.
        // And quantity is expecting a float, but value is passed as string
        $variables = [
            'cartId' => $cartId,
            'sku' => ['123.78'],
            'quantity' => '5.60'
        ];
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Variable "$sku" got invalid value ["123.78"]; ' .
            'String cannot represent a non string value: ["123.78"]');
        $this->graphQlMutation($query, $variables);
    }

    /**
     * Verify that query doesn't return error when an integer is passed for a field where string is expected
     *
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product_with_numeric_sku.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     */
    public function testFloatExpectedWhenNonNumericStringProvided()
    {
        $cartId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->addProductsToCart();

        //  quantity is expecting a float, but a non-numeric string is passed
        $variables = [
            'cartId' => $cartId,
            'sku' => '123.78',
            'quantity' => 'ten'
        ];
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Variable "$quantity" got invalid value "ten"; ' .
            'Float cannot represent non numeric value: "ten"');
        $this->graphQlMutation($query, $variables);
    }

    /**
     *  Query the cart to get the cart item id
     *
     * @param string $cartId
     * @return string
     * @throws \Exception
     */
    private function getCartItemId(string $cartId): string
    {
        $cartQuery = <<<QUERY
{
  cart(cart_id: "$cartId") {
    id
    items {
      id
    }
  }
}
QUERY;
        $result = $this->graphQlQuery($cartQuery);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertCount(1, $result['cart']['items']);
        return $result['cart']['items'][0]['id'];
    }

    /**
     * @return string
     */
    private function addProductsToCart():string
    {
        return <<<'MUTATION'
mutation AddItemsToCart($cartId: String!, $sku: String!, $quantity: Float!)
{
  addProductsToCart (
     cartId:$cartId
     cartItems:[
      {
        sku:$sku
        quantity:$quantity
    }]
  )
  {
    cart {
      id
      total_quantity
      items { product {sku id } quantity}
    }
  }
}
MUTATION;
    }
}
