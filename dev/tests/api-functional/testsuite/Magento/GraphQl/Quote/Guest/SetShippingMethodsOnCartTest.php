<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Guest;

use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Quote\Model\Quote\Address as QuoteAddress;

/**
 * Test for setting shipping methods on cart for guest
 */
class SetShippingMethodsOnCartTest extends GraphQlAbstract
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
     * @var QuoteAddress
     */
    private $quoteAddress;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->quoteResource = $objectManager->get(QuoteResource::class);
        $this->quoteFactory = $objectManager->get(QuoteFactory::class);
        $this->quoteIdToMaskedId = $objectManager->get(QuoteIdToMaskedQuoteIdInterface::class);
        $this->quoteAddress = $objectManager->get(QuoteAddress::class);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_virtual_product_saved.php
     */
    public function testShippingMethodWithVirtualProduct()
    {
        $methodCode = 'flatrate';
        $carrierCode = 'flatrate';
        $reservedOrderId = 'test_order_with_virtual_product_without_address';

        $maskedQuoteId = $this->getMaskedQuoteIdByReversedQuoteId($reservedOrderId);
        $quoteAddressId = $this->getQuoteAddressIdByReversedQuoteId($reservedOrderId);

        $query = $this->prepareMutationQuery(
            $maskedQuoteId,
            $methodCode,
            $carrierCode,
            $quoteAddressId
        );

        self::expectException(\Exception::class);
        self::expectExceptionMessage('Carrier with such method not found: ' . $methodCode . ', ' . $methodCode . '');
        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     */
    public function testShippingMethodWithSimpleProduct()
    {
        $methodCode = 'flatrate';
        $carrierCode = 'flatrate';
        $reservedOrderId = 'test_order_with_simple_product_without_address';

        $maskedQuoteId = $this->getMaskedQuoteIdByReversedQuoteId($reservedOrderId);
        $quoteAddressId = $this->getQuoteAddressIdByReversedQuoteId($reservedOrderId);

        $query = $this->prepareMutationQuery(
            $maskedQuoteId,
            $methodCode,
            $carrierCode,
            $quoteAddressId
        );

        $response = $this->graphQlQuery($query);

        self::assertArrayHasKey('setShippingMethodsOnCart', $response);
        self::assertArrayHasKey('cart', $response['setShippingMethodsOnCart']);
        self::assertArrayHasKey('shipping_addresses', $response['setShippingMethodsOnCart']['cart']);

        $shippingMethod = current($response['setShippingMethodsOnCart']['cart']['shipping_addresses']);
        self::assertArrayHasKey('selected_shipping_method', $shippingMethod);
        self::assertEquals($carrierCode, $shippingMethod['selected_shipping_method']['carrier_code']);
        self::assertEquals($methodCode, $shippingMethod['selected_shipping_method']['method_code']);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @expectedException \Exception
     * @expectedExceptionMessage Required parameter "cart_address_id" is missing.
     */
    public function testShippingMethodWithSimpleProductWithoutAddress()
    {
        $methodCode = 'flatrate';
        $carrierCode = 'flatrate';
        $reservedOrderId = 'test_order_with_simple_product_without_address';

        $maskedQuoteId = $this->getMaskedQuoteIdByReversedQuoteId($reservedOrderId);
        $quoteAddressId = 0;

        $query = $this->prepareMutationQuery(
            $maskedQuoteId,
            $methodCode,
            $carrierCode,
            $quoteAddressId
        );

        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @expectedException \Exception
     * @expectedExceptionMessage Required parameter "method_code" is missing.
     */
    public function testSetShippingMethodWithMissedRequiredParameters()
    {
        $methodCode = '';
        $carrierCode = 'flatrate';
        $reservedOrderId = 'test_order_with_simple_product_without_address';

        $maskedQuoteId = $this->getMaskedQuoteIdByReversedQuoteId($reservedOrderId);
        $quoteAddressId = $this->getQuoteAddressIdByReversedQuoteId($reservedOrderId);

        $query = $this->prepareMutationQuery(
            $maskedQuoteId,
            $methodCode,
            $carrierCode,
            $quoteAddressId
        );

        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/guest_quote_with_addresses.php
     */
    public function testSetNonExistentShippingMethod()
    {
        $methodCode = 'non-existed-method-code';
        $carrierCode = 'non-carrier-method-code';
        $reservedOrderId = 'guest_quote';

        $maskedQuoteId = $this->getMaskedQuoteIdByReversedQuoteId($reservedOrderId);

        /** @var Quote $quote */
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, 'guest_quote', 'reserved_order_id');
        $quoteAddressId = (int) $quote->getShippingAddress()->getId();

        $query = $this->prepareMutationQuery(
            $maskedQuoteId,
            $methodCode,
            $carrierCode,
            $quoteAddressId
        );

        self::expectException(\Exception::class);
        self::expectExceptionMessage('Carrier with such method not found: ' . $carrierCode . ', ' . $methodCode . '');
        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/guest_quote_with_addresses.php
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_virtual_product_and_address.php
     */
    public function testSetShippingMethodIfAddressIsNotBelongToCart()
    {
        $methodCode = 'flatrate';
        $carrierCode = 'flatrate';
        $reservedOrderId = 'guest_quote';

        $maskedQuoteId = $this->getMaskedQuoteIdByReversedQuoteId($reservedOrderId);

        /** @var Quote $quote */
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, 'test_order_with_virtual_product', 'reserved_order_id');
        $quoteAddressId = (int) $quote->getShippingAddress()->getId();

        $query = $this->prepareMutationQuery(
            $maskedQuoteId,
            $methodCode,
            $carrierCode,
            $quoteAddressId
        );

        self::expectException(\Exception::class);
        self::expectExceptionMessage('The current user cannot use cart address with ID "' . $quoteAddressId . '"');
        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/guest_quote_with_addresses.php
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with cartId = 0
     */
    public function testSetShippingMethodToNonExistentCart()
    {
        $methodCode = 'flatrate';
        $carrierCode = 'flatrate';
        $reservedOrderId = 'non_existent_cart_reversed_quote_id';

        $maskedQuoteId = $this->getMaskedQuoteIdByReversedQuoteId($reservedOrderId);
        $quoteAddressId = 1;

        $query = $this->prepareMutationQuery(
            $maskedQuoteId,
            $methodCode,
            $carrierCode,
            $quoteAddressId
        );

        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/guest_quote_with_addresses.php
     */
    public function testSetShippingMethodToGuestCart()
    {
        $methodCode = 'flatrate';
        $carrierCode = 'flatrate';
        $reservedOrderId = 'guest_quote';

        $maskedQuoteId = $this->getMaskedQuoteIdByReversedQuoteId($reservedOrderId);

        /** @var Quote $quote */
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, $reservedOrderId, 'reserved_order_id');
        $quoteAddressId = (int) $quote->getShippingAddress()->getId();

        $query = $this->prepareMutationQuery(
            $maskedQuoteId,
            $methodCode,
            $carrierCode,
            $quoteAddressId
        );

        $response = $this->graphQlQuery($query);

        self::assertArrayHasKey('setShippingMethodsOnCart', $response);
        self::assertArrayHasKey('cart', $response['setShippingMethodsOnCart']);
        self::assertArrayHasKey('shipping_addresses', $response['setShippingMethodsOnCart']['cart']);

        $shippingMethod = current($response['setShippingMethodsOnCart']['cart']['shipping_addresses']);
        self::assertArrayHasKey('selected_shipping_method', $shippingMethod);
        self::assertEquals($carrierCode, $shippingMethod['selected_shipping_method']['carrier_code']);
        self::assertEquals($methodCode, $shippingMethod['selected_shipping_method']['method_code']);
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/guest_quote_with_addresses.php
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_virtual_product_and_address.php
     */
    public function testSetShippingMethodToAnotherCustomerCart()
    {
        $methodCode = 'flatrate';
        $carrierCode = 'flatrate';
        $reservedOrderId = 'guest_quote';

        $maskedQuoteId = $this->getMaskedQuoteIdByReversedQuoteId('test_order_with_virtual_product');

        /** @var Quote $quote */
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, $reservedOrderId, 'reserved_order_id');
        $quoteAddressId = (int) $quote->getShippingAddress()->getId();

        $query = $this->prepareMutationQuery(
            $maskedQuoteId,
            $methodCode,
            $carrierCode,
            $quoteAddressId
        );

        self::expectException(\Exception::class);
        self::expectExceptionMessage(
            'The current user cannot perform operations on cart "' . $maskedQuoteId . '"'
        );
        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/guest_quote_with_addresses.php
     */
    public function testSetShippingMethodToNonExistentCartAddress()
    {
        $methodCode = 'flatrate';
        $carrierCode = 'flatrate';
        $reservedOrderId = 'guest_quote';

        $maskedQuoteId = $this->getMaskedQuoteIdByReversedQuoteId($reservedOrderId);

        /** @var Quote $quote */
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, $reservedOrderId, 'reserved_order_id');
        $quoteAddressId = 1963425585;

        $query = $this->prepareMutationQuery(
            $maskedQuoteId,
            $methodCode,
            $carrierCode,
            $quoteAddressId
        );

        self::expectException(\Exception::class);
        self::expectExceptionMessage(
            'Could not find a cart address with ID "' . $quoteAddressId . '"'
        );
        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/guest_quote_with_addresses.php
     */
    public function testSetShippingMethodToGuestCartAddress()
    {
        $methodCode = 'flatrate';
        $carrierCode = 'flatrate';
        $reservedOrderId = 'guest_quote';

        $maskedQuoteId = $this->getMaskedQuoteIdByReversedQuoteId($reservedOrderId);

        /** @var Quote $quote */
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, $reservedOrderId, 'reserved_order_id');
        $quoteAddressId = (int) $quote->getShippingAddress()->getId();

        $query = $this->prepareMutationQuery(
            $maskedQuoteId,
            $methodCode,
            $carrierCode,
            $quoteAddressId
        );

        $response = $this->graphQlQuery($query);

        self::assertArrayHasKey('setShippingMethodsOnCart', $response);
        self::assertArrayHasKey('cart', $response['setShippingMethodsOnCart']);
        self::assertArrayHasKey('shipping_addresses', $response['setShippingMethodsOnCart']['cart']);

        $shippingMethod = current($response['setShippingMethodsOnCart']['cart']['shipping_addresses']);
        self::assertArrayHasKey('selected_shipping_method', $shippingMethod);
        self::assertEquals($carrierCode, $shippingMethod['selected_shipping_method']['carrier_code']);
        self::assertEquals($methodCode, $shippingMethod['selected_shipping_method']['method_code']);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     */
    public function testSetShippingMethodToAnotherCustomerCartAddress()
    {
        $methodCode = 'flatrate';
        $carrierCode = 'flatrate';
        $reservedOrderId = 'test_order_1';

        $maskedQuoteId = $this->getMaskedQuoteIdByReversedQuoteId($reservedOrderId);

        /** @var Quote $quote */
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, $reservedOrderId, 'reserved_order_id');
        $quoteAddressId = (int) $quote->getShippingAddress()->getId();

        $query = $this->prepareMutationQuery(
            $maskedQuoteId,
            $methodCode,
            $carrierCode,
            $quoteAddressId
        );

        self::expectException(\Exception::class);
        self::expectExceptionMessage(
            'The current user cannot perform operations on cart "' . $maskedQuoteId . '"'
        );
        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/guest_quote_with_addresses.php
     * @magentoApiDataFixture Magento/Checkout/_files/enable_all_shipping_methods.php
     * @expectedException \Exception
     * @expectedExceptionMessage You cannot specify multiple shipping methods.
     */
    public function testSetMultipleShippingMethods()
    {
        $methodCode = 'flatrate';
        $carrierCode = 'flatrate';
        $reservedOrderId = 'guest_quote';

        $maskedQuoteId = $this->getMaskedQuoteIdByReversedQuoteId($reservedOrderId);

        /** @var Quote $quote */
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, $reservedOrderId, 'reserved_order_id');
        $shippingAddressId = (int) $quote->getShippingAddress()->getId();

        $query = <<<QUERY
mutation {
  setShippingMethodsOnCart(input:
    {
      cart_id: "$maskedQuoteId",
      shipping_methods: [{
          cart_address_id: $shippingAddressId
          method_code: "$methodCode"
          carrier_code: "$carrierCode"
        },
        {
          cart_address_id: $shippingAddressId
          method_code: "ups"
          carrier_code: "ups"
      }]
    }) {

    cart {
      shipping_addresses {
        address_id
        firstname
        lastname
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

        $this->graphQlQuery($query);
    }

    /**
     * @param string $maskedQuoteId
     * @param string $shippingMethodCode
     * @param string $shippingCarrierCode
     * @param int $shippingAddressId
     * @return string
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function prepareMutationQuery(
        string $maskedQuoteId,
        string $shippingMethodCode,
        string $shippingCarrierCode,
        int $shippingAddressId
    ) : string {
        return <<<QUERY
mutation {
  setShippingMethodsOnCart(input: 
    {
      cart_id: "$maskedQuoteId", 
      shipping_methods: [{
        cart_address_id: $shippingAddressId
        carrier_code: "$shippingCarrierCode"
        method_code: "$shippingMethodCode"
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
     * @param string $reversedQuoteId
     * @return string
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function getMaskedQuoteIdByReversedQuoteId(string $reversedQuoteId): string
    {
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, $reversedQuoteId, 'reserved_order_id');

        return $this->quoteIdToMaskedId->execute((int)$quote->getId());
    }

    /**
     * @param string $reversedQuoteId
     * @return int
     */
    private function getQuoteAddressIdByReversedQuoteId(string $reversedQuoteId): int
    {
        $guestAddress = $this->quoteAddress->setData([
            'firstname'=> 'John',
            'lastname'=> 'Smith',
            'company'=> 'Company Name',
            'street'=> 'Green str, 67',
            'city'=> 'CityM',
            'region' => 'AL',
            'postcode'=> 75477,
            'telephone'=> 3468676,
            'country_id'=> 'US',
            'region_id' => 1
        ]);

        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, $reversedQuoteId, 'reserved_order_id');

        $quote->setBillingAddress($guestAddress);
        $quote->setShippingAddress($guestAddress);
        $quote->collectTotals();
        $this->quoteResource->save($quote);

        return (int) $quote->getShippingAddress()->getId();
    }
}
