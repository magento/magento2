<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Guest;

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
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_billing_address.php
     */
    public function testGetCartWithBillingAddress()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId('test_quote');
        $response = $this->graphQlQuery($this->getGetBillingAddressQuery($maskedQuoteId));

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
        ];

        self::assertEquals($expectedBillingAddressData, $response['cart']['billing_address']);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Catalog/_files/products.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_billing_address.php
     */
    public function testGetBillingAddressFromAnotherCustomerCart()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId('test_quote');
        $query = $this->getGetBillingAddressQuery($maskedQuoteId);

        $this->expectExceptionMessage(
            "The current user cannot perform operations on cart \"$maskedQuoteId\""
        );

        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     */
    public function testGetBillingAddressIfBillingAddressIsNotSet()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId('test_quote');
        $query = $this->getGetBillingAddressQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query);

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
        ];

        self::assertEquals($expectedBillingAddressData, $response['cart']['billing_address']);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Could not find a cart with ID "non_existent_masked_id"
     */
    public function testGetBillingAddressOfNonExistentCart()
    {
        $maskedQuoteId = 'non_existent_masked_id';
        $query = $this->getGetBillingAddressQuery($maskedQuoteId);
        $this->graphQlQuery($query);
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
    }
  }
}
QUERY;
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
