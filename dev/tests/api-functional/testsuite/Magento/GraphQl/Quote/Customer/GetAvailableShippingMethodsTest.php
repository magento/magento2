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
 * Test for get available shipping methods
 */
class GetAvailableShippingMethodsTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedId;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager              = Bootstrap::getObjectManager();
        $this->quoteFactory         = $objectManager->get(QuoteFactory::class);
        $this->quoteResource        = $objectManager->create(QuoteResource::class);
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
        $this->quoteIdToMaskedId    = $objectManager->create(QuoteIdToMaskedQuoteIdInterface::class);
    }

    /**
     * Test case: get available shipping methods from current customer quote
     *
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     */
    public function testGetAvailableShippingMethods()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId('test_order_1');
        $response      = $this->graphQlQuery($this->getQuery($maskedQuoteId), [], '', $this->getHeaderMap());

        self::assertArrayHasKey('cart', $response);
        self::assertArrayHasKey('shipping_addresses', $response['cart']);
        self::assertCount(1, $response['cart']['shipping_addresses']);
        self::assertArrayHasKey('available_shipping_methods', $response['cart']['shipping_addresses'][0]);
        self::assertCount(1, $response['cart']['shipping_addresses'][0]['available_shipping_methods']);

        $expectedAddressData = [
            'amount'         => 10,
            'base_amount'    => 10,
            'carrier_code'   => 'flatrate',
            'carrier_title'  => 'Flat Rate',
            'error_message'  => '',
            'method_code'    => 'flatrate',
            'method_title'   => 'Fixed',
            'price_incl_tax' => 10,
            'price_excl_tax' => 10,
        ];
        self::assertEquals(
            $expectedAddressData,
            $response['cart']['shipping_addresses'][0]['available_shipping_methods'][0]
        );
    }

    /**
     * Test case: get available shipping methods from quote of another customer
     *
     * @magentoApiDataFixture Magento/Customer/_files/three_customers.php
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     */
    public function testGetAvailableShippingMethodsFromAnotherCustomerCart()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId('test_order_1');

        $this->expectExceptionMessage(
            "The current user cannot perform operations on cart \"$maskedQuoteId\""
        );

        $this->graphQlQuery($this->getQuery($maskedQuoteId), [], '', $this->getHeaderMap('customer2@search.example.com'));
    }

    /**
     * Test case: get available shipping methods when all shipping methods are disabled
     *
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     * @magentoApiDataFixture Magento/Checkout/_files/disable_all_active_shipping_methods.php
     */
    public function testGetAvailableShippingMethodsIfShippingsAreNotSet()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId('test_order_1');
        $response      = $this->graphQlQuery($this->getQuery($maskedQuoteId), [], '', $this->getHeaderMap());

        self::assertEquals(0, count($response['cart']['shipping_addresses'][0]['available_shipping_methods']));
    }

    /**
     * Test case: get available shipping methods from non-existent cart
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @expectedException \Exception
     * @expectedExceptionMessage Could not find a cart with ID "non_existent_masked_id"
     */
    public function testGetAvailableShippingMethodsOfNonExistentCart()
    {
        $maskedQuoteId = 'non_existent_masked_id';
        $query = $this->getQuery($maskedQuoteId);

        $this->graphQlQuery($query, [], '', $this->getHeaderMap());
    }

    /**
     * @param string $maskedQuoteId
     * @return string
     */
    private function getQuery(
        string $maskedQuoteId
    ): string {
        return <<<QUERY
query {
  cart (cart_id: "{$maskedQuoteId}") {
    shipping_addresses {
        available_shipping_methods {
          amount
          base_amount
          carrier_code
          carrier_title
          error_message
          method_code
          method_title
          price_excl_tax
          price_incl_tax
        }
    }
  }
}
QUERY;
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

    /**
     * @param string $reservedOrderId
     * @return string
     */
    private function getMaskedQuoteIdByReservedOrderId(string $reservedOrderId): string
    {
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, $reservedOrderId, 'reserved_order_id');

        return $this->quoteIdToMaskedId->execute((int)$quote->getId());
    }
}
