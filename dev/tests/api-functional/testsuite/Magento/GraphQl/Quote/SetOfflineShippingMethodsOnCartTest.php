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
 * Test for setting offline shipping methods on cart
 */
class SetOfflineShippingMethodsOnCartTest extends GraphQlAbstract
{
    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var QuoteResource
     */
    private $quoteResource;

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
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/enable_offline_shipping_methods.php
     * @magentoApiDataFixture Magento/OfflineShipping/_files/tablerates_weight.php
     *
     * @param string $carrierCode
     * @param string $methodCode
     * @param float $amount
     * @param string $label
     * @dataProvider offlineShippingMethodDataProvider
     */
    public function testSetOfflineShippingMethod(string $carrierCode, string $methodCode, float $amount, string $label)
    {
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load(
            $quote,
            'test_order_1',
            'reserved_order_id'
        );
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$quote->getId());
        $shippingAddressId = (int)$quote->getShippingAddress()->getId();

        $query = $this->getQuery(
            $maskedQuoteId,
            $shippingAddressId,
            $carrierCode,
            $methodCode
        );

        $response = $this->sendRequestWithToken($query);

        $addressesInformation = $response['setShippingMethodsOnCart']['cart']['shipping_addresses'];
        self::assertEquals($addressesInformation[0]['selected_shipping_method']['carrier_code'], $carrierCode);
        self::assertEquals($addressesInformation[0]['selected_shipping_method']['method_code'], $methodCode);
        self::assertEquals($addressesInformation[0]['selected_shipping_method']['amount'], $amount);
        self::assertEquals($addressesInformation[0]['selected_shipping_method']['label'], $label);
    }

    /**
     * @return array
     */
    public function offlineShippingMethodDataProvider()
    {
        return [
            'flatrate_flatrate' => ['flatrate', 'flatrate', 10, 'Flat Rate - Fixed'],
            'tablerate_bestway' => ['tablerate', 'bestway', 10, 'Best Way - Table Rate'],
            'freeshipping_freeshipping' => ['freeshipping', 'freeshipping', 0, 'Free Shipping - Free'],
        ];
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/enable_offline_shipping_methods.php
     */
    public function testSetShippingMethodTwiceInOneRequest()
    {
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load(
            $quote,
            'test_order_1',
            'reserved_order_id'
        );
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddressId = $shippingAddress->getId();
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$quote->getId());

        $query = <<<QUERY
mutation {
  setShippingMethodsOnCart(input: {
    cart_id: "$maskedQuoteId"
    shipping_methods: [
      {
        cart_address_id: $shippingAddressId
        carrier_code: "flatrate"
        method_code: "flatrate"
      }
      {
        cart_address_id: $shippingAddressId
        carrier_code: "freeshipping"
        method_code: "freeshipping"
      }
    ]
  }) {
    cart {
      shipping_addresses {
        selected_shipping_method {
          carrier_code
          method_code
          label
          amount
        }
      }
    } 
  }
}
QUERY;
        self::expectExceptionMessage('You cannot specify multiple shipping methods.');
        $this->sendRequestWithToken($query);
    }

    /**
     * Generates query for setting the specified shipping method on cart
     *
     * @param int $shippingAddressId
     * @param string $maskedQuoteId
     * @param string $carrierCode
     * @param string $methodCode
     * @return string
     */
    private function getQuery(
        string $maskedQuoteId,
        int $shippingAddressId,
        string $carrierCode,
        string $methodCode
    ): string {
        return <<<QUERY
mutation {
  setShippingMethodsOnCart(input: {
    cart_id: "$maskedQuoteId"
    shipping_methods: [
      {
        cart_address_id: $shippingAddressId
        carrier_code: "$carrierCode"
        method_code: "$methodCode"
      }
    ]
  }) {
    cart {
      shipping_addresses {
        selected_shipping_method {
          carrier_code
          method_code
          label
          amount
        }
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
}
