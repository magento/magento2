<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\OfflineShipping;

use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for setting offline shipping methods on cart
 */
class SetOfflineShippingOnCartTest extends GraphQlAbstract
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
     * @magentoApiDataFixture Magento/OfflineShipping/_files/tablerates_weight.php
     * @magentoApiDataFixture Magento/Checkout/_files/enable_all_shipping_methods.php
     * @dataProvider offlineShippingMethodDataProvider()
     * @param string $carrier
     * @param string $method
     * @param float $amount
     * @param string $label
     */
    public function testSetOfflineShippingMethod(string $carrier, string $method, float $amount, string $label)
    {
        $this->setShippingMethodAndCheckResponse(
            $carrier,
            $method,
            $amount,
            $label
        );
    }

    /**
     * Data provider for base offline shipping methods
     *
     * @return array
     */
    public function offlineShippingMethodDataProvider()
    {
        return [
            ['flatrate', 'flatrate', 10, 'Flat Rate - Fixed'],
            ['tablerate', 'bestway', 10, 'Best Way - Table Rate'],
            ['freeshipping', 'freeshipping', 0, 'Free Shipping - Free']
        ];
    }

    /**
     * Send request for setting the requested shipping method and check the output
     *
     * @param string $shippingCarrierCode
     * @param string $shippingMethodCode
     * @param string $shippingAmount
     * @param string $shippingLabel
     * @throws \Magento\Framework\Exception\AuthenticationException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function setShippingMethodAndCheckResponse(
        string $shippingCarrierCode,
        string $shippingMethodCode,
        float $shippingAmount,
        string $shippingLabel
    ) {
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load(
            $quote,
            'test_order_1',
            'reserved_order_id'
        );
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddressId = $shippingAddress->getId();
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$quote->getId());

        $query = $this->getQuery(
            $maskedQuoteId,
            $shippingMethodCode,
            $shippingCarrierCode,
            $shippingAddressId
        );

        $response = $this->sendRequestWithToken($query);

        $addressesInformation = $response['setShippingMethodsOnCart']['cart']['shipping_addresses'];
        self::assertEquals($addressesInformation[0]['selected_shipping_method']['carrier_code'], $shippingCarrierCode);
        self::assertEquals($addressesInformation[0]['selected_shipping_method']['method_code'], $shippingMethodCode);
        self::assertEquals($addressesInformation[0]['selected_shipping_method']['amount'], $shippingAmount);
        self::assertEquals($addressesInformation[0]['selected_shipping_method']['label'], $shippingLabel);
    }

    /**
     * Generates query for setting the specified shipping method on cart
     *
     * @param string $maskedQuoteId
     * @param string $shippingMethodCode
     * @param string $shippingCarrierCode
     * @param string $shippingAddressId
     * @return string
     */
    private function getQuery(
        string $maskedQuoteId,
        string $shippingMethodCode,
        string $shippingCarrierCode,
        string $shippingAddressId
    ) : string {
        return <<<QUERY
mutation {
  setShippingMethodsOnCart(input: 
    {
      cart_id: "$maskedQuoteId", 
      shipping_methods: [{
        cart_address_id: $shippingAddressId
        method_code: "$shippingMethodCode"
        carrier_code: "$shippingCarrierCode"
      }]
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