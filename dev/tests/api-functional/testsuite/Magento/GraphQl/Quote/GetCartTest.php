<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

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
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testGetCartForGuest()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReversedQuoteId('test_order_1');
        $query = $this->getCartQuery($maskedQuoteId);

        $response = $this->graphQlQuery($query);

        self::assertArrayHasKey('cart', $response);
        self::assertEquals($maskedQuoteId, $response['cart']['cart_id']);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_items_saved.php
     */
    public function testGetCartByRegisteredCustomer()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReversedQuoteId('test_order_item_with_items');
        $query = $this->getCartQuery($maskedQuoteId);

        $response = $this->graphQlQuery($query, [], '', $this->getHeaderMap());

        self::assertArrayHasKey('cart', $response);
        self::assertEquals($maskedQuoteId, $response['cart']['cart_id']);
        self::assertNotEmpty($response['cart']['items']);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_items_saved.php
     */
    public function testGetCartOfAnotherCustomerByGuest()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReversedQuoteId('test_order_item_with_items');
        $query = $this->getCartQuery($maskedQuoteId);

        self:$this->expectExceptionMessage(
            "The current user cannot perform operations on cart \"{$maskedQuoteId}\""
        );
        $this->graphQlQuery($query);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Could not find a cart with ID "non_existent_masked_id"
     */
    public function testGetNonExistentCart()
    {
        $maskedQuoteId = 'non_existent_masked_id';
        $query = $this->getCartQuery($maskedQuoteId);

        $this->graphQlQuery($query);
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
    cart_id
    applied_coupon {
        code
    }
    items {
      id
    }
    shipping_addresses {
      firstname,
      lastname
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
