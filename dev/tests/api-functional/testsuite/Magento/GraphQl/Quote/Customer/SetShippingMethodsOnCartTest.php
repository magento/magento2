<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Customer;

use Magento\Catalog\Model\Product;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Model\Quote;
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
     * @var \Magento\Quote\Model\Quote\Address\Rate
     */
    private $rate;

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
        $this->rate = $objectManager->get(\Magento\Quote\Model\Quote\Address\Rate::class);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_virtual_product_and_address.php
     * @magentoApiDataFixture Magento/Checkout/_files/enable_all_shipping_methods.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_virtual.php
     * @throws \Exception
     */
    public function testShippingMethodWithVirtualProduct()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReversedQuoteId('test_order_with_virtual_product');

        /** @var Quote $quote */
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, 'test_order_with_virtual_product', 'reserved_order_id');

        $shippingAddress = $quote->getShippingAddress();
        $rate = $this->rate;

        $rate->setPrice(2)
            ->setAddressId($shippingAddress->getId())
            ->setCode('flatrate_flatrate');
        $shippingAddress->setShippingMethod('flatrate_flatrate')
            ->addShippingRate($rate)
            ->save();

        $mutation = $this->prepareMutationQuery(
            $maskedQuoteId,
            'flatrate',
            'flatrate_flatrate',
            '1'
        );

        $this->graphQlQuery($mutation, [], '', $this->getHeaderMap());
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
      shipping_methods: [{
        cart_address_id: $shippingAddressId
        method_code: "$shippingMethodCode"
        carrier_code: "$shippingCarrierCode"
      }]
    } ) 
    {
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
