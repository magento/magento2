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
 * Test for setting shipping methods on cart
 */
class SetShippingMethodOnCartTest extends GraphQlAbstract
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
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     */
    public function testSetShippingMethodOnCart()
    {
        $shippingCarrierCode = 'flatrate';
        $shippingMethodCode = 'flatrate';
        $this->quoteResource->load(
            $this->quote,
            'test_order_1',
            'reserved_order_id'
        );
        $shippingAddress = $this->quote->getShippingAddress();
        $shippingAddressId = $shippingAddress->getId();
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());

        $query = $this->prepareMutationQuery(
            $maskedQuoteId,
            $shippingMethodCode,
            $shippingCarrierCode,
            $shippingAddressId
        );

        $response = $this->sendRequestWithToken($query);

        self::assertArrayHasKey('setShippingMethodsOnCart', $response);
        self::assertArrayHasKey('cart', $response['setShippingMethodsOnCart']);
        self::assertEquals($maskedQuoteId, $response['setShippingMethodsOnCart']['cart']['cart_id']);
        $addressesInformation = $response['setShippingMethodsOnCart']['cart']['shipping_addresses'];
        self::assertCount(1, $addressesInformation);
        self::assertEquals(
            $addressesInformation[0]['selected_shipping_method']['code'],
            $shippingCarrierCode . '_' . $shippingMethodCode
        );
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     */
    public function testSetShippingMethodWithWrongCartId()
    {
        $shippingCarrierCode = 'flatrate';
        $shippingMethodCode = 'flatrate';
        $shippingAddressId = '1';
        $maskedQuoteId = 'invalid';

        $query = $this->prepareMutationQuery(
            $maskedQuoteId,
            $shippingMethodCode,
            $shippingCarrierCode,
            $shippingAddressId
        );

        self::expectExceptionMessage("Could not find a cart with ID \"$maskedQuoteId\"");
        $this->sendRequestWithToken($query);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     */
    public function testSetNonExistingShippingMethod()
    {
        $shippingCarrierCode = 'non';
        $shippingMethodCode = 'existing';
        $this->quoteResource->load(
            $this->quote,
            'test_order_1',
            'reserved_order_id'
        );
        $shippingAddress = $this->quote->getShippingAddress();
        $shippingAddressId = $shippingAddress->getId();
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());

        $query = $this->prepareMutationQuery(
            $maskedQuoteId,
            $shippingMethodCode,
            $shippingCarrierCode,
            $shippingAddressId
        );

        self::expectExceptionMessage("Carrier with such method not found: $shippingCarrierCode, $shippingMethodCode");
        $this->sendRequestWithToken($query);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     */
    public function testSetShippingMethodWithNonExistingAddress()
    {
        $shippingCarrierCode = 'flatrate';
        $shippingMethodCode = 'flatrate';
        $this->quoteResource->load(
            $this->quote,
            'test_order_1',
            'reserved_order_id'
        );
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());
        $shippingAddressId = '-20';

        $query = $this->prepareMutationQuery(
            $maskedQuoteId,
            $shippingMethodCode,
            $shippingCarrierCode,
            $shippingAddressId
        );

        self::expectExceptionMessage('The shipping address is missing. Set the address and try again.');
        $this->sendRequestWithToken($query);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     */
    public function testSetShippingMethodByGuestToCustomerCart()
    {
        $shippingCarrierCode = 'flatrate';
        $shippingMethodCode = 'flatrate';
        $this->quoteResource->load(
            $this->quote,
            'test_order_1',
            'reserved_order_id'
        );
        $shippingAddress = $this->quote->getShippingAddress();
        $shippingAddressId = $shippingAddress->getId();
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());

        $query = $this->prepareMutationQuery(
            $maskedQuoteId,
            $shippingMethodCode,
            $shippingCarrierCode,
            $shippingAddressId
        );

        self::expectExceptionMessage(
            "The current user cannot perform operations on cart \"$maskedQuoteId\""
        );

        $this->graphQlQuery($query);
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
    private function prepareMutationQuery(
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
      shipping_methods: [
        {
          shipping_method_code: "$shippingMethodCode"
          shipping_carrier_code: "$shippingCarrierCode"
          cart_address_id: $shippingAddressId
        }
      ]}) {
    
    cart {
      cart_id,
      shipping_addresses {
        selected_shipping_method {
          code
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
