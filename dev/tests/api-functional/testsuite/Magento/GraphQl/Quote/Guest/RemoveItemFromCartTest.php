<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Guest;

use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\GraphQl\Quote\GetQuoteItemIdByReservedQuoteIdAndSku;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for removeItemFromCartTest mutation
 */
class RemoveItemFromCartTest extends GraphQlAbstract
{
    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    /**
     * @var GetQuoteItemIdByReservedQuoteIdAndSku
     */
    private $getQuoteItemIdByReservedQuoteIdAndSku;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
        $this->getQuoteItemIdByReservedQuoteIdAndSku = $objectManager->get(
            GetQuoteItemIdByReservedQuoteIdAndSku::class
        );
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testRemoveItemFromCart()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $itemId = $this->getQuoteItemIdByReservedQuoteIdAndSku->execute('test_quote', 'simple_product');

        $query = $this->getQuery($maskedQuoteId, $itemId);
        $response = $this->graphQlMutation($query);

        $this->assertArrayHasKey('removeItemFromCart', $response);
        $this->assertArrayHasKey('cart', $response['removeItemFromCart']);
        $this->assertCount(0, $response['removeItemFromCart']['cart']['items']);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Could not find a cart with ID "non_existent_masked_id"
     */
    public function testRemoveItemFromNonExistentCart()
    {
        $query = $this->getQuery('non_existent_masked_id', 1);
        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testRemoveNonExistentItem()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $notExistentItemId = 999;

        $this->expectExceptionMessage("Cart doesn't contain the {$notExistentItemId} item.");

        $query = $this->getQuery($maskedQuoteId, $notExistentItemId);
        $this->graphQlMutation($query);
    }

    /**
     * _security
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_virtual_product_saved.php
     */
    public function testRemoveItemIfItemIsNotBelongToCart()
    {
        $firstQuoteMaskedId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $secondQuoteItemId = $this->getQuoteItemIdByReservedQuoteIdAndSku->execute(
            'test_order_with_virtual_product_without_address',
            'virtual-product'
        );

        $this->expectExceptionMessage("Cart doesn't contain the {$secondQuoteItemId} item.");

        $query = $this->getQuery($firstQuoteMaskedId, $secondQuoteItemId);
        $this->graphQlMutation($query);
    }

    /**
     * _security
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testRemoveItemFromCustomerCart()
    {
        $customerQuoteMaskedId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $customerQuoteItemId = $this->getQuoteItemIdByReservedQuoteIdAndSku->execute('test_quote', 'simple_product');

        $this->expectExceptionMessage("The current user cannot perform operations on cart \"$customerQuoteMaskedId\"");

        $query = $this->getQuery($customerQuoteMaskedId, $customerQuoteItemId);
        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Required parameter "cart_id" is missing
     */
    public function testWithoutRequiredCartIdParameter()
    {
        $maskedQuoteId = '';
        $itemId = $this->getQuoteItemIdByReservedQuoteIdAndSku->execute('test_quote', 'simple_product');

        $query = $this->getQuery($maskedQuoteId, $itemId);
        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Required parameter "cart_item_id" is missing.
     */
    public function testWithoutRequiredCartItemIdParameter()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $itemId = 0;

        $query = $this->getQuery($maskedQuoteId, $itemId);
        $this->graphQlMutation($query);
    }

    /**
     * @param string $maskedQuoteId
     * @param int $itemId
     * @return string
     */
    private function getQuery(string $maskedQuoteId, int $itemId): string
    {
        return <<<QUERY
mutation {
  removeItemFromCart(
    input: {
      cart_id: "{$maskedQuoteId}"
      cart_item_id: {$itemId}
    }
  ) {
    cart {
      items {
        quantity
      }
    }
  }
}
QUERY;
    }
}
