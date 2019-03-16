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
     * @magentoApiDataFixture Magento/Sales/_files/guest_quote_with_addresses.php
     */
    public function testGetCartWithBillingAddress()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId('guest_quote');
        $response = $this->graphQlQuery($this->getGetBillingAddressQuery($maskedQuoteId));

        $expectedBillingAddressData = [
            'firstname' => 'John',
            'lastname' => 'Smith',
            'company' => null,
            'street' => [
                'Black str, 48'
            ],
            'city' => 'CityX',
            'region' => [
                'code' => 'AL',
                'label' => 'Alabama',
            ],
            'postcode' => '47676',
            'country' => [
                'code' => 'US',
                'label' => 'US',
            ],
            'telephone' => '3234676',
            'address_type' => 'BILLING',
        ];

        self::assertEquals($expectedBillingAddressData, $response['cart']['billing_address']);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     */
    public function testGetBillingAddressFromAnotherCustomerCart()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId('test_order_1');
        $query = $this->getGetBillingAddressQuery($maskedQuoteId);

        $this->expectExceptionMessage(
            "The current user cannot perform operations on cart \"$maskedQuoteId\""
        );

        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     */
    public function testGetBillingAddressIfBillingAddressIsNotSet()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId('test_order_with_simple_product_without_address');
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
