<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Guest;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

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
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->quoteResource = $objectManager->get(QuoteResource::class);
        $this->quoteFactory = $objectManager->get(QuoteFactory::class);
        $this->quoteIdToMaskedId = $objectManager->get(QuoteIdToMaskedQuoteIdInterface::class);
    }

    public function testShippingMethodWithVirtualProduct()
    {
        $this->markTestIncomplete('https://github.com/magento/graphql-ce/issues/422');
    }

    public function testShippingMethodWithSimpleProduct()
    {
        $this->markTestIncomplete('https://github.com/magento/graphql-ce/issues/422');
    }

    public function testShippingMethodWithSimpleProductWithoutAddress()
    {
        $this->markTestIncomplete('https://github.com/magento/graphql-ce/issues/422');
    }

    public function testSetShippingMethodWithMissedRequiredParameters()
    {
        $this->markTestIncomplete('https://github.com/magento/graphql-ce/issues/422');
    }

    public function testSetNonExistentShippingMethod()
    {
        $this->markTestIncomplete('https://github.com/magento/graphql-ce/issues/422');
    }

    public function testSetShippingMethodIfAddressIsNotBelongToCart()
    {
        $this->markTestIncomplete('https://github.com/magento/graphql-ce/issues/422');
    }

    public function testSetShippingMethodToNonExistentCart()
    {
        $this->markTestIncomplete('https://github.com/magento/graphql-ce/issues/422');
    }

    public function testSetShippingMethodToGuestCart()
    {
        $this->markTestIncomplete('https://github.com/magento/graphql-ce/issues/422');
    }

    public function testSetShippingMethodToAnotherCustomerCart()
    {
        $this->markTestIncomplete('https://github.com/magento/graphql-ce/issues/422');
    }

    public function testSetShippingMethodToNonExistentCartAddress()
    {
        $this->markTestIncomplete('https://github.com/magento/graphql-ce/issues/422');
    }

    public function testSetShippingMethodToGuestCartAddress()
    {
        $this->markTestIncomplete('https://github.com/magento/graphql-ce/issues/422');
    }

    public function testSetShippingMethodToAnotherCustomerCartAddress()
    {
        $this->markTestIncomplete('https://github.com/magento/graphql-ce/issues/422');
    }

    public function testSetMultipleShippingMethods()
    {
        $this->markTestIncomplete('https://github.com/magento/graphql-ce/issues/422');
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_shipping_method.php
     */
    public function testReSetShippingMethod()
    {
        $maskedQuoteId = $this->unAssignCustomerFromQuoteAndShippingAddress('test_order_1');
        $methodCode = 'flatrate';
        $carrierCode = 'flatrate';
        $quote = $this->getQuoteByReversedQuoteId('test_order_1');
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddressId = $shippingAddress->getId();
        $query = $this->prepareMutationQuery($maskedQuoteId, $methodCode, $carrierCode, $shippingAddressId);

        $response = $this->graphQlQuery($query);

        self::assertArrayHasKey('setShippingMethodsOnCart', $response);
        self::assertArrayHasKey('cart', $response['setShippingMethodsOnCart']);
        self::assertArrayHasKey('shipping_addresses', $response['setShippingMethodsOnCart']['cart']);
        foreach ($response['setShippingMethodsOnCart']['cart']['shipping_addresses'] as $address) {
            self::assertArrayHasKey('address_id', $address);
            if ($address['address_id'] == $shippingAddressId) {
                self::assertArrayHasKey('selected_shipping_method', $address);
                self::assertEquals($methodCode, $address['selected_shipping_method']['method_code']);
                self::assertEquals($carrierCode, $address['selected_shipping_method']['carrier_code']);
            }
        }
    }

    /**
     * @param string $maskedQuoteId
     * @param string $shippingMethodCode
     * @param string $shippingCarrierCode
     * @param string $shippingAddressId
     * @return string
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
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
        address_id
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
     * @param int $customerId
     * @return string
     */
    private function unAssignCustomerFromQuoteAndShippingAddress(
        string $reversedQuoteId
    ): string {
        /** @var Quote $quote */
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, $reversedQuoteId, 'reserved_order_id');
        $quote->setCustomerId(0);

        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setCustomerId(0);
        $shippingAddress->setCustomerAddressId(0);

        $this->quoteResource->save($quote);

        return $this->quoteIdToMaskedId->execute((int)$quote->getId());
    }

    /**
     * @param string $reversedQuoteId
     * @return Quote
     */
    private function getQuoteByReversedQuoteId(string $reversedQuoteId): Quote
    {
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, $reversedQuoteId, 'reserved_order_id');

        return $quote;
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
}
