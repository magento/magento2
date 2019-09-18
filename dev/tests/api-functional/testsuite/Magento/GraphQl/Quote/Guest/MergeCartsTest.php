<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Guest;

use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for merging guest carts
 */
class MergeCartsTest extends GraphQlAbstract
{
    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedId;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->quoteResource = $objectManager->get(QuoteResource::class);
        $this->quoteFactory = $objectManager->get(QuoteFactory::class);
        $this->quoteIdToMaskedId = $objectManager->get(QuoteIdToMaskedQuoteIdInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_virtual_product_saved.php
     */
    public function testMergeGuestCarts()
    {
        $firstQuote = $this->quoteFactory->create();
        $this->quoteResource->load($firstQuote, 'test_order_with_simple_product_without_address', 'reserved_order_id');

        $secondQuote = $this->quoteFactory->create();
        $this->quoteResource->load($secondQuote, 'test_order_with_virtual_product_without_address', 'reserved_order_id');

        $firstMaskedId = $this->quoteIdToMaskedId->execute((int)$firstQuote->getId());
        $secondMaskedId = $this->quoteIdToMaskedId->execute((int)$secondQuote->getId());

        $query = $this->getCartMergeMutation($firstMaskedId, $secondMaskedId);
        $mergeResponse = $this->graphQlMutation($query);

        self::assertArrayHasKey('mergeCarts', $mergeResponse);
        $maskedQuoteId = $mergeResponse['mergeCarts'];
        self::assertNotEquals($firstMaskedId, $maskedQuoteId);
        self::assertNotEquals($secondMaskedId, $maskedQuoteId);

        $cartResponse = $this->graphQlMutation($this->getCartQuery($maskedQuoteId));

        self::assertArrayHasKey('cart', $cartResponse);
        self::assertArrayHasKey('items', $cartResponse['cart']);
        self::assertCount(2, $cartResponse['cart']['items']);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_virtual_product_saved.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @expectedException \Exception
     * @expectedExceptionMessage The current user cannot perform operations on cart
     */
    public function testMergeGuestWithCustomerCart()
    {
        $firstQuote = $this->quoteFactory->create();
        $this->quoteResource->load($firstQuote, 'test_order_with_virtual_product_without_address', 'reserved_order_id');

        $secondQuote = $this->quoteFactory->create();
        $this->quoteResource->load($secondQuote, 'test_quote', 'reserved_order_id');

        $firstMaskedId = $this->quoteIdToMaskedId->execute((int)$firstQuote->getId());
        $secondMaskedId = $this->quoteIdToMaskedId->execute((int)$secondQuote->getId());

        $query = $this->getCartMergeMutation($firstMaskedId, $secondMaskedId);
        $this->graphQlMutation($query);
    }

    /**
     * Create the mergeCart mutation
     *
     * @param string $firstMaskedId
     * @param string $secondMaskedId
     * @return string
     */
    private function getCartMergeMutation(string $firstMaskedId, string $secondMaskedId): string
    {
        return <<<QUERY
mutation {
  mergeCarts(input: {
    first_cart_id: "{$firstMaskedId}"
    second_cart_id: "{$secondMaskedId}"
  })
}
QUERY;
    }

    /**
     * Get cart query
     *
     * @param string $maskedId
     * @return string
     */
    private function getCartQuery(string $maskedId): string
    {
        return <<<QUERY
{
  cart(cart_id: "{$maskedId}") {
    items {
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
