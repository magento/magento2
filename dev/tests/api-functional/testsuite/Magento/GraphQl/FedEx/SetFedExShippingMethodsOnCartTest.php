<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\FedEx;

use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for setting "FedEx" shipping method on cart
 */
class SetFedExShippingMethodsOnCartTest extends GraphQlAbstract
{
    /**
     * Defines carrier code for "FedEx" shipping method
     */
    const CARRIER_CODE = 'fedex';

    /**
     * Defines method code for the "Ground" FedEx shipping
     */
    const CARRIER_METHOD_CODE_GROUND = 'FEDEX_GROUND';

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
     * @magentoApiDataFixture Magento/FedEx/_files/enable_fedex_shipping_method.php
     */
    public function testSetFedExShippingMethod()
    {
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, 'test_order_1', 'reserved_order_id');
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$quote->getId());
        $shippingAddressId = (int)$quote->getShippingAddress()->getId();

        $query = $this->getAddFedExShippingMethodQuery(
            $maskedQuoteId,
            $shippingAddressId,
            self::CARRIER_CODE,
            self::CARRIER_METHOD_CODE_GROUND
        );

        $response = $this->sendRequestWithToken($query);
        $addressesInformation = $response['setShippingMethodsOnCart']['cart']['shipping_addresses'];
        $expectedResult = [
            'carrier_code' => self::CARRIER_CODE,
            'method_code' => self::CARRIER_METHOD_CODE_GROUND,
            'label' => 'Federal Express - Ground',
        ];
        self::assertEquals($addressesInformation[0]['selected_shipping_method'], $expectedResult);
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
    private function getAddFedExShippingMethodQuery(
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
