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
use Magento\Quote\Model\Quote;

/**
 * Test for setting shipping methods on cart for customer
 */
class SetShippingMethodsOnCartTest extends GraphQlAbstract
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

    public function testShippingMethodWithVirtualProduct()
    {
        $this->markTestIncomplete('https://github.com/magento/graphql-ce/issues/423');
    }

    public function testShippingMethodWithSimpleProduct()
    {
        $this->markTestIncomplete('https://github.com/magento/graphql-ce/issues/423');
    }

    public function testShippingMethodWithSimpleProductWithoutAddress()
    {
        $this->markTestIncomplete('https://github.com/magento/graphql-ce/issues/423');
    }

    public function testSetShippingMethodWithMissedRequiredParameters()
    {
        $this->markTestIncomplete('https://github.com/magento/graphql-ce/issues/423');
    }

    public function testSetNonExistentShippingMethod()
    {
        $this->markTestIncomplete('https://github.com/magento/graphql-ce/issues/423');
    }

    public function testSetShippingMethodIfAddressIsNotBelongToCart()
    {
        $this->markTestIncomplete('https://github.com/magento/graphql-ce/issues/423');
    }

    public function testSetShippingMethodToNonExistentCart()
    {
        $this->markTestIncomplete('https://github.com/magento/graphql-ce/issues/423');
    }

    public function testSetShippingMethodToGuestCart()
    {
        $this->markTestIncomplete('https://github.com/magento/graphql-ce/issues/423');
    }

    public function testSetShippingMethodToAnotherCustomerCart()
    {
        $this->markTestIncomplete('https://github.com/magento/graphql-ce/issues/423');
    }

    public function testSetShippingMethodToNonExistentCartAddress()
    {
        $this->markTestIncomplete('https://github.com/magento/graphql-ce/issues/423');
    }

    public function testSetShippingMethodToGuestCartAddress()
    {
        $this->markTestIncomplete('https://github.com/magento/graphql-ce/issues/423');
    }

    public function testSetShippingMethodToAnotherCustomerCartAddress()
    {
        $this->markTestIncomplete('https://github.com/magento/graphql-ce/issues/423');
    }

    public function testSetMultipleShippingMethods()
    {
        $this->markTestIncomplete('https://github.com/magento/graphql-ce/issues/423');
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_shipping_method.php
     */
    public function testReSetShippingMethod()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReversedQuoteId('test_order_1');
        $methodCode = 'flatrate';
        $carrierCode = 'flatrate';
        $quote = $this->getQuoteByReversedQuoteId('test_order_1');
        $shippingAddressId = $quote->getShippingAddress()->getId();
        
        $query = $this->prepareMutationQuery($maskedQuoteId, $methodCode, $carrierCode, $shippingAddressId);
        $response = $this->graphQlQuery($query, [], '', $this->getHeaderMap());

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
     * @param int $customerId
     * @return string
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function assignQuoteToCustomer(
        string $reversedQuoteId,
        int $customerId
    ): string {
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, $reversedQuoteId, 'reserved_order_id');
        $quote->setCustomerId($customerId);
        $this->quoteResource->save($quote);
        return $this->quoteIdToMaskedId->execute((int)$quote->getId());
    }

    /**
     * @param string $username
     * @param string $password
     * @return array
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function getHeaderMap(string $username = 'customer@example.com', string $password = 'password'): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($username, $password);
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];
        return $headerMap;
    }
}
