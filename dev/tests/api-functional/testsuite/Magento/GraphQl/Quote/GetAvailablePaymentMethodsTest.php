<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for getting cart information
 */
class GetAvailablePaymentMethodsTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @var Quote
     */
    private $quote;

    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedId;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->quoteResource = $objectManager->create(QuoteResource::class);
        $this->quote = $objectManager->create(Quote::class);
        $this->quoteIdToMaskedId = $objectManager->create(QuoteIdToMaskedQuoteIdInterface::class);
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_items_saved.php
     */
    public function testGetCartWithPaymentMethodsForRegisteredCustomer()
    {
        $reservedOrderId = 'test_order_item_with_items';
        $this->quoteResource->load(
            $this->quote,
            $reservedOrderId,
            'reserved_order_id'
        );

        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());
        $query = $this->prepareGetCartQuery($maskedQuoteId);

        $response = $this->sendRequestWithToken($query);

        self::assertArrayHasKey('cart', $response);
        self::assertNotEmpty($response['cart']['items']);
        self::assertNotEmpty($response['cart']['available_payment_methods']);
        self::assertCount(1, $response['cart']['available_payment_methods']);
        self::assertEquals('checkmo', $response['cart']['available_payment_methods'][0]['code']);
        self::assertEquals('Check / Money order', $response['cart']['available_payment_methods'][0]['title']);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testGetCartWithPaymentMethodsForGuest()
    {
        $reservedOrderId = 'test_order_1';
        $this->quoteResource->load(
            $this->quote,
            $reservedOrderId,
            'reserved_order_id'
        );

        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());
        $query = $this->prepareGetCartQuery($maskedQuoteId);

        $response = $this->graphQlQuery($query);

        self::assertArrayHasKey('cart', $response);

        self::assertNotEmpty($response['cart']['available_payment_methods']);
        self::assertCount(2, $response['cart']['available_payment_methods']);
        self::assertEquals('checkmo', $response['cart']['available_payment_methods'][0]['code']);
        self::assertEquals('Check / Money order', $response['cart']['available_payment_methods'][0]['title']);
        self::assertEquals('free', $response['cart']['available_payment_methods'][1]['code']);
        self::assertEquals(
            'No Payment Information Required',
            $response['cart']['available_payment_methods'][1]['title']
        );
    }

    /**
     * Generates query for setting the specified shipping method on cart
     *
     * @param string $maskedQuoteId
     * @return string
     */
    private function prepareGetCartQuery(
        string $maskedQuoteId
    ) : string {
        return <<<QUERY
{
  cart(cart_id: "$maskedQuoteId") {
    applied_coupon {
    	  code
    }
    items {
      id
    }
    available_payment_methods {
      code,
      title
    }
  }
}

QUERY;
    }

    /**
     * Sends a GraphQL request with using a bearer token
     *
     * @param string $query
     * @return array
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    private function sendRequestWithToken(string $query): array
    {

        $customerToken = $this->customerTokenService->createCustomerAccessToken('customer@example.com', 'password');
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];

        return $this->graphQlQuery($query, [], '', $headerMap);
    }
}
