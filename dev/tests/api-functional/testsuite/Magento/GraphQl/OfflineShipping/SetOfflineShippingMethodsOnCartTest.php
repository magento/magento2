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
 * Test for setting offline shipping methods on cart
 */
class SetOfflineShippingOnCartTest extends GraphQlAbstract
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
     * Test for general routine of setting a shipping method on shopping cart
     *
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     * @magentoApiDataFixture Magento/Checkout/_files/enable_all_shipping_methods.php
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
        $addressesInformation = $response['setShippingMethodsOnCart']['cart']['shipping_addresses'];
        self::assertCount(1, $addressesInformation);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     * @magentoApiDataFixture Magento/Checkout/_files/enable_all_shipping_methods.php
     */
    public function testSetFlatrateOnCart()
    {
        $this->setShippingMethodAndCheckResponse(
            'flatrate',
            'flatrate',
            '10',
            'Flat Rate - Fixed'
        );
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     * @magentoApiDataFixture Magento/OfflineShipping/_files/tablerates_weight.php
     * @magentoApiDataFixture Magento/Checkout/_files/enable_all_shipping_methods.php
     */
    public function testSetTableRatesOnCart()
    {
        $this->setShippingMethodAndCheckResponse(
            'tablerate',
            'bestway',
            '10',
            'Best Way - Table Rate'
        );
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     * @magentoApiDataFixture Magento/Checkout/_files/enable_all_shipping_methods.php
     */
    public function testSetFreeShippingOnCart()
    {
        $this->setShippingMethodAndCheckResponse(
            'freeshipping',
            'freeshipping',
            '0',
            'Free Shipping - Free'
        );
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     * @magentoApiDataFixture Magento/Checkout/_files/enable_all_shipping_methods.php
     */
    public function testSetUpsOnCart()
    {
        $this->setShippingMethodAndCheckResponse(
            'ups',
            'GND',
            '15.61',
            'United Parcel Service - Ground'
        );
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     * @magentoApiDataFixture Magento/Checkout/_files/enable_all_shipping_methods.php
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
     * @magentoApiDataFixture Magento/Checkout/_files/enable_all_shipping_methods.php
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
     * @magentoApiDataFixture Magento/Checkout/_files/enable_all_shipping_methods.php
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

        self::expectExceptionMessage("Could not find a cart address with ID \"$shippingAddressId\"");
        $this->sendRequestWithToken($query);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     * @magentoApiDataFixture Magento/Checkout/_files/enable_all_shipping_methods.php
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
        string $shippingAmount,
        string $shippingLabel
    ) {
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