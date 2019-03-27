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
      shipping_addresses: [{
        cart_address_id: $shippingAddressId
        shipping_method: {
          method_code: "$shippingMethodCode"
          carrier_code: "$shippingCarrierCode"
        }
      }]
      }) {
    
    cart {
      cart_id,
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
     * @param string $reversedOrderId
     * @return string
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function getMaskedQuoteIdByReservedOrderId(string $reversedOrderId): string
    {
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, $reversedOrderId, 'reserved_order_id');

        return $this->quoteIdToMaskedId->execute((int)$quote->getId());
    }

    /**
     * @param string $reversedOrderId
     * @param int $customerId
     * @return string
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function assignQuoteToCustomer(
        string $reversedOrderId,
        int $customerId
    ): string {
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, $reversedOrderId, 'reserved_order_id');
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
