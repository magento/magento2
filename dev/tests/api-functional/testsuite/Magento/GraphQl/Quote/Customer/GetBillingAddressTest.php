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
 * Test for get billing address
 */
class GetBillingAddressTest extends GraphQlAbstract
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
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     */
    public function testGetCartWithBillingAddress()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId('test_quote');
        $query = $this->getGetBillingAddressQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query, [], '', $this->getHeaderMap());

        $expectedBillingAddressData = [
            'firstname' => 'John',
            'lastname' => 'Smith',
            'company' => 'CompanyName',
            'street' => [
                'Green str, 67'
            ],
            'city' => 'CityM',
            'region' => [
                'code' => 'AL',
                'label' => 'Alabama',
            ],
            'postcode' => '75477',
            'country' => [
                'code' => 'US',
                'label' => 'US',
            ],
            'telephone' => '3468676',
            'address_type' => 'BILLING',
            'customer_notes' => null,
        ];

        self::assertEquals($expectedBillingAddressData, $response['cart']['billing_address']);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/three_customers.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testGetBillingAddressFromAnotherCustomerCart()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId('test_quote');
        $this->expectExceptionMessage(
            "The current user cannot perform operations on cart \"$maskedQuoteId\""
        );
        $this->graphQlQuery(
            $this->getGetBillingAddressQuery($maskedQuoteId),
            [],
            '',
            $this->getHeaderMap('customer2@search.example.com')
        );
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testGetBillingAddressIfBillingAddressIsNotSet()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId('test_quote');
        $query = $this->getGetBillingAddressQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query, [], '', $this->getHeaderMap());

        $expectedBillingAddressData = [
            'firstname' => null,
            'lastname' => null,
            'company' => null,
            'street' => [
                ''
            ],
            'city' => null,
            'region' => [
                'code' => null,
                'label' => null,
            ],
            'postcode' => null,
            'country' => [
                'code' => null,
                'label' => null,
            ],
            'telephone' => null,
            'address_type' => 'BILLING',
            'customer_notes' => null,
        ];

        self::assertEquals($expectedBillingAddressData, $response['cart']['billing_address']);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @expectedException \Exception
     * @expectedExceptionMessage Could not find a cart with ID "non_existent_masked_id"
     */
    public function testGetBillingAddressOfNonExistentCart()
    {
        $maskedQuoteId = 'non_existent_masked_id';
        $query = $this->getGetBillingAddressQuery($maskedQuoteId);

        $this->graphQlQuery($query, [], '', $this->getHeaderMap());
    }

    /**
     * @param string $maskedQuoteId
     * @return string
     */
    private function getGetBillingAddressQuery(
        string $maskedQuoteId
    ): string {
        return <<<QUERY
{
  cart(cart_id: "$maskedQuoteId") {
    billing_address {
      firstname
      lastname
      company
      street
      city
      region 
      {
        code
        label
      }
      postcode
      country 
      {
        code
        label
      }
      telephone
      address_type
      customer_notes
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
