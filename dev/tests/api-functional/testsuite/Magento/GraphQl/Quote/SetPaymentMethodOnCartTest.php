<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\OfflinePayments\Model\Checkmo;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for setting payment methods on cart
 */
class SetPaymentMethodOnCartTest extends GraphQlAbstract
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
     * @var QuoteFactory
     */
    private $quoteFactory;

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
        $this->quoteResource = $objectManager->get(QuoteResource::class);
        $this->quoteFactory = $objectManager->get(QuoteFactory::class);
        $this->quoteIdToMaskedId = $objectManager->get(QuoteIdToMaskedQuoteIdInterface::class);
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     */
    public function testSetPaymentMethodOnCart()
    {
        $methodCode = Checkmo::PAYMENT_METHOD_CHECKMO_CODE;
        $maskedQuoteId = $this->getMaskedQuoteIdByReversedQuoteId('test_order_1');

        $query = $this->prepareMutationQuery($maskedQuoteId, $methodCode);
        $response = $this->sendRequestWithToken($query);

        self::assertArrayHasKey('setPaymentMethodOnCart', $response);
        self::assertArrayHasKey('cart', $response['setPaymentMethodOnCart']);
        self::assertEquals($maskedQuoteId, $response['setPaymentMethodOnCart']['cart']['cart_id']);
        self::assertArrayHasKey('selected_payment_method', $response['setPaymentMethodOnCart']['cart']);
        self::assertEquals($methodCode, $response['setPaymentMethodOnCart']['cart']['selected_payment_method']['code']);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     * @expectedException \Exception
     * @expectedExceptionMessage The requested Payment Method is not available.
     */
    public function testSetNonExistingPaymentMethod()
    {
        $methodCode = 'noway';
        $maskedQuoteId = $this->getMaskedQuoteIdByReversedQuoteId('test_order_1');

        $query = $this->prepareMutationQuery($maskedQuoteId, $methodCode);
        $this->sendRequestWithToken($query);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     */
    public function testSetPaymentMethodByGuestToCustomerCart()
    {
        $methodCode = 'checkmo';
        $maskedQuoteId = $this->getMaskedQuoteIdByReversedQuoteId('test_order_1');

        $query = $this->prepareMutationQuery($maskedQuoteId, $methodCode);

        $this->expectExceptionMessage(
            "The current user cannot perform operations on cart \"$maskedQuoteId\""
        );

        $this->graphQlQuery($query);
    }

    /**
     * Generates query for setting the specified shipping method on cart
     *
     * @param string $maskedQuoteId
     * @param string $methodCode
     * @return string
     */
    private function prepareMutationQuery(
        string $maskedQuoteId,
        string $methodCode
    ) : string {
        return <<<QUERY
mutation {
  setPaymentMethodOnCart(input: 
    {
      cart_id: "$maskedQuoteId", 
      payment_method: {
          code: "$methodCode"
        }
      }) {
    
    cart {
      cart_id,
      selected_payment_method {
        code
      }
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
}
