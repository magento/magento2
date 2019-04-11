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
 * Test getting cart totals for registered customer
 */
class CartTotalsTest extends GraphQlAbstract
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

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->quoteResource = $objectManager->get(QuoteResource::class);
        $this->quoteFactory = $objectManager->get(QuoteFactory::class);
        $this->quoteIdToMaskedId = $objectManager->get(QuoteIdToMaskedQuoteIdInterface::class);
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Tax/_files/tax_rule_for_region_1.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/apply_tax_for_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_billing_address.php
     */
    public function testGetCartTotalsWithTaxApplied()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReversedQuoteId('test_quote');
        $query = $this->getCartTotalsGraphqlQuery($maskedQuoteId);
        $response = $this->sendRequestWithToken($query);

        self::assertArrayHasKey('prices', $response['cart']);
        $pricesResponse = $response['cart']['prices'];
        self::assertEquals(21.5, $pricesResponse['grand_total']['value']);
        self::assertEquals(21.5, $pricesResponse['subtotal_including_tax']['value']);
        self::assertEquals(20, $pricesResponse['subtotal_excluding_tax']['value']);
        self::assertEquals(20, $pricesResponse['subtotal_with_discount_excluding_tax']['value']);

        $appliedTaxesResponse = $pricesResponse['applied_taxes'];

        self::assertEquals('US-TEST-*-Rate-1', $appliedTaxesResponse[0]['label']);
        self::assertEquals(1.5, $appliedTaxesResponse[0]['amount']['value']);
        self::assertEquals('USD', $appliedTaxesResponse[0]['amount']['currency']);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_billing_address.php
     */
    public function testGetTotalsWithNoTaxApplied()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReversedQuoteId('test_quote');
        $query = $this->getCartTotalsGraphqlQuery($maskedQuoteId);
        $response = $this->sendRequestWithToken($query);

        $pricesResponse = $response['cart']['prices'];
        self::assertEquals(20, $pricesResponse['grand_total']['value']);
        self::assertEquals(20, $pricesResponse['subtotal_including_tax']['value']);
        self::assertEquals(20, $pricesResponse['subtotal_excluding_tax']['value']);
        self::assertEquals(20, $pricesResponse['subtotal_with_discount_excluding_tax']['value']);
        self::assertEmpty($pricesResponse['applied_taxes']);
    }

    /**
     * The totals calculation is based on quote address.
     * But the totals should be calculated even if no address is set
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testGetCartTotalsWithNoAddressSet()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReversedQuoteId('test_quote');
        $query = $this->getCartTotalsGraphqlQuery($maskedQuoteId);
        $response = $this->sendRequestWithToken($query);

        $pricesResponse = $response['cart']['prices'];
        self::assertEquals(20, $pricesResponse['grand_total']['value']);
        self::assertEquals(20, $pricesResponse['subtotal_including_tax']['value']);
        self::assertEquals(20, $pricesResponse['subtotal_excluding_tax']['value']);
        self::assertEquals(20, $pricesResponse['subtotal_with_discount_excluding_tax']['value']);
        self::assertEmpty($pricesResponse['applied_taxes']);
    }

    /**
     * Generates GraphQl query for retrieving cart totals
     *
     * @param string $maskedQuoteId
     * @return string
     */
    private function getCartTotalsGraphqlQuery(string $maskedQuoteId): string
    {
        return <<<QUERY
{
  cart(cart_id: "$maskedQuoteId") {
    prices {
      grand_total {
        value,
        currency
      }
      subtotal_including_tax {
        value
        currency
      }
      subtotal_excluding_tax {
        value
        currency
      }
      subtotal_with_discount_excluding_tax {
        value
        currency
      }
      applied_taxes {
        label
        amount {
          value
          currency
        }
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
