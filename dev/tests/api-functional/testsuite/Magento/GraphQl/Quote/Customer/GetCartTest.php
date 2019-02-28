<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Customer;

use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for getting cart information
 */
class GetCartTest extends GraphQlAbstract
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

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->quoteResource = $objectManager->get(QuoteResource::class);
        $this->quoteFactory = $objectManager->get(QuoteFactory::class);
        $this->quoteIdToMaskedId = $objectManager->get(QuoteIdToMaskedQuoteIdInterface::class);
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_items_saved.php
     */
    public function testGetCart()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReversedQuoteId('test_order_item_with_items');
        $query = $this->getCartQuery($maskedQuoteId);

        $response = $this->graphQlQuery($query, [], '', $this->getHeaderMap());

        self::assertArrayHasKey('cart', $response);
        self::assertArrayHasKey('items', $response['cart']);
        self::assertCount(2, $response['cart']['items']);

        self::assertNotEmpty($response['cart']['items'][0]['id']);
        self::assertEquals($response['cart']['items'][0]['qty'], 2);
        self::assertEquals($response['cart']['items'][0]['product']['sku'], 'simple');

        self::assertNotEmpty($response['cart']['items'][1]['id']);
        self::assertEquals($response['cart']['items'][1]['qty'], 1);
        self::assertEquals($response['cart']['items'][1]['product']['sku'], 'simple_one');
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testGetGuestCart()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReversedQuoteId('test_order_with_simple_product_without_address');
        $query = $this->getCartQuery($maskedQuoteId);

        $this->expectExceptionMessage(
            "The current user cannot perform operations on cart \"{$maskedQuoteId}\""
        );
        $this->graphQlQuery($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/three_customers.php
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_items_saved.php
     */
    public function testGetAnotherCustomerCart()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReversedQuoteId('test_order_item_with_items');
        $query = $this->getCartQuery($maskedQuoteId);

        $this->expectExceptionMessage(
            "The current user cannot perform operations on cart \"{$maskedQuoteId}\""
        );
        $this->graphQlQuery($query, [], '', $this->getHeaderMap('customer2@search.example.com'));
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @expectedException \Exception
     * @expectedExceptionMessage Could not find a cart with ID "non_existent_masked_id"
     */
    public function testGetNonExistentCart()
    {
        $maskedQuoteId = 'non_existent_masked_id';
        $query = $this->getCartQuery($maskedQuoteId);

        $this->graphQlQuery($query, [], '', $this->getHeaderMap());
    }

    /**
     * @param string $maskedQuoteId
     * @return string
     */
    private function getCartQuery(
        string $maskedQuoteId
    ) : string {
        return <<<QUERY
{
  cart(cart_id: "$maskedQuoteId") {
    items {
      id
      qty
      product {
        sku
      }
    }
  }
}
QUERY;
    }

    /**
     * @param string $reversedQuoteId
     * @return string
     */
    private function getMaskedQuoteIdByReversedQuoteId(string $reversedQuoteId): string
    {
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, $reversedQuoteId, 'reserved_order_id');

        return $this->quoteIdToMaskedId->execute((int)$quote->getId());
    }

    /**
     * @param string $username
     * @param string $password
     * @return array
     */
    private function getHeaderMap(string $username = 'customer@example.com', string $password = 'password'): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($username, $password);
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];
        return $headerMap;
    }
}
