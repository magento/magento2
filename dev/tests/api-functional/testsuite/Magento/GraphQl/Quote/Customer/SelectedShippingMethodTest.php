<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Customer;

use Magento\TestFramework\TestCase\GraphQlAbstract;

class SelectedShippingMethodTest extends GraphQlAbstract
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote
     */
    private $quoteResource;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var \Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedId;

    /**
     * @var \Magento\Integration\Api\CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @param $response
     * @return mixed
     */
    public function getSelectedShippingMethod($response)
    {
        return $response['cart']['shipping_addresses'][0]['selected_shipping_method'];
    }

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->filterBuilder = $this->objectManager->create(
            \Magento\Framework\Api\FilterBuilder::class
        );
        $this->sortOrderBuilder = $this->objectManager->create(
            \Magento\Framework\Api\SortOrderBuilder::class
        );
        $this->searchCriteriaBuilder = $this->objectManager->create(
            \Magento\Framework\Api\SearchCriteriaBuilder::class
        );
        $this->quoteResource = $this->objectManager->get(
            \Magento\Quote\Model\ResourceModel\Quote::class
        );
        $this->quoteFactory = $this->objectManager->get(
            \Magento\Quote\Model\QuoteFactory::class
        );
        $this->quoteIdToMaskedId = $this->objectManager->get(
            \Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface::class
        );
        $this->customerTokenService = $this->objectManager->get(
            \Magento\Integration\Api\CustomerTokenServiceInterface::class
        );
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_shipping_method.php
     */
    public function testGetCartWithShippingMethod()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId('test_order_1');
        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query, [], '', $this->getHeaderMap());

        self::assertArrayHasKey('cart', $response);
        self::assertInternalType(
            'array',
            $response['cart']['shipping_addresses'][0]['selected_shipping_method'],
            'There are no selected shipping method for customer cart!'
        );
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_shipping_method.php
     */
    public function testGetShippingMethodFromCustomerCart()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId('test_order_1');
        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query, [], '', $this->getHeaderMap());

        self::assertArrayHasKey('cart', $response);

        $selectedShippingMethod = $this->getSelectedShippingMethod($response);

        self::assertEquals('flatrate', $selectedShippingMethod['carrier_code']);
        self::assertEquals('flatrate', $selectedShippingMethod['method_code']);
        self::assertEquals('Flat Rate - Fixed', $selectedShippingMethod['label']);
        self::assertEquals(0, $selectedShippingMethod['amount']);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address.php
     */
    public function testGetShippingMethodIfShippingMethodIsNotSet()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId('test_order_1');
        $query = $this->getQuery($maskedQuoteId);

        $response = $this->graphQlQuery($query, [], '', $this->getHeaderMap());

        self::assertArrayHasKey('cart', $response);

        $selectedShippingMethod = $this->getSelectedShippingMethod($response);

        self::assertNull($selectedShippingMethod['carrier_code']);
        self::assertNull($selectedShippingMethod['method_code']);
        self::assertNull($selectedShippingMethod['label']);
        self::assertNull($selectedShippingMethod['amount']);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address.php
     */
    public function testGetShippingMethodOfNonExistentCart()
    {
        $query = $this->getQuery('nonExistentCart');
        self::expectException(\Magento\TestFramework\TestCase\GraphQl\ResponseContainsErrorsException::class);
        self::expectExceptionMessage(
            'GraphQL response contains errors: Could not find a cart with ID "nonExistentCart"'
        );
        $this->graphQlQuery($query, [], '', $this->getHeaderMap());
    }

    /**
     * Retrieve quote by given reserved order ID
     *
     * @param string $reservedOrderId
     * @return \Magento\Quote\Model\Quote
     * @throws \InvalidArgumentException
     */
    protected function getCart($reservedOrderId)
    {
        /** @var $cart \Magento\Quote\Model\Quote */
        $cart = $this->objectManager->get(\Magento\Quote\Model\Quote::class);
        $cart->load($reservedOrderId, 'reserved_order_id');
        if (!$cart->getId()) {
            throw new \InvalidArgumentException('There is no quote with provided reserved order ID.');
        }
        return $cart;
    }

    /**
     * @param string $reservedOrderId
     * @return string
     */
    private function getMaskedQuoteIdByReservedOrderId(string $reservedOrderId): string
    {
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, $reservedOrderId, 'reserved_order_id');

        return $this->quoteIdToMaskedId->execute((int)$quote->getId());
    }

    /**
     * @param string $username
     * @param string $password
     * @return array
     */
    private function getHeaderMap(string $username = 'customer@example.com', string $password = 'password'): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($username, $password);
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];
        return $headerMap;
    }

    /**
     * @param string $maskedQuoteId
     * @return string
     */
    private function getQuery(
        string $maskedQuoteId
    ): string {
        return <<<QUERY
{
  cart(cart_id: "$maskedQuoteId") {
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
QUERY;
    }
}
