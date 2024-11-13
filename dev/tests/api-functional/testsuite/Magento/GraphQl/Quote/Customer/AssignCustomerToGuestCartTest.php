<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Customer;

use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Quote\Model\GetQuoteByReservedOrderId;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Integration\Api\CustomerTokenServiceInterface;

/**
 * Test for assigning customer to the guest cart
 */
class AssignCustomerToGuestCartTest extends GraphQlAbstract
{
    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedId;

    /**
     * @var GetQuoteByReservedOrderId
     */
    private $getQuoteByReservedOrderId;

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->quoteIdToMaskedId = $objectManager->get(QuoteIdToMaskedQuoteIdInterface::class);
        $this->getQuoteByReservedOrderId = $objectManager->get(GetQuoteByReservedOrderId::class);
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
    }

    /**
     * Test for assigning customer to the guest cart
     *
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_virtual_product_saved.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testAssignCustomerToGuestCart(): void
    {
        $guestQuote = $this->getQuoteByReservedOrderId->execute('test_order_with_virtual_product_without_address');
        $guestQuoteItem = $guestQuote->getAllVisibleItems()[0];
        $guestQuoteMaskedId = $this->quoteIdToMaskedId->execute((int)$guestQuote->getId());

        $customerQuote = $this->getQuoteByReservedOrderId->execute('test_quote');
        $customerQuoteItem = $customerQuote->getAllVisibleItems()[0];

        $response = $this->graphQlMutation(
            $this->getAssignCustomerToGuestCartMutation($guestQuoteMaskedId),
            [],
            '',
            $this->getHeaderMap()
        );
        $this->assertArrayHasKey('assignCustomerToGuestCart', $response);
        $this->assertArrayHasKey('items', $response['assignCustomerToGuestCart']);
        $items = $response['assignCustomerToGuestCart']['items'];
        $this->assertCount(2, $items);

        $this->assertEquals($customerQuoteItem->getQty(), $items[1]['quantity']);
        $this->assertEquals($customerQuoteItem->getSku(), $items[1]['product']['sku']);

        $this->assertEquals($guestQuoteItem->getQty(), $items[0]['quantity']);
        $this->assertEquals($guestQuoteItem->getSku(), $items[0]['product']['sku']);
    }

    /**
     * Test that customer cart is expired after assigning
     *
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_virtual_product_saved.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testCustomerCartExpiryAfterAssigning(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The cart isn\'t active.');

        $guestQuote = $this->getQuoteByReservedOrderId->execute('test_order_with_virtual_product_without_address');
        $guestQuoteMaskedId = $this->quoteIdToMaskedId->execute((int)$guestQuote->getId());

        $customerQuote = $this->getQuoteByReservedOrderId->execute('test_quote');
        $customerQuoteMaskedId = $this->quoteIdToMaskedId->execute((int)$customerQuote->getId());

        $this->graphQlMutation(
            $this->getAssignCustomerToGuestCartMutation($guestQuoteMaskedId),
            [],
            '',
            $this->getHeaderMap()
        );
        $this->graphQlMutation(
            $this->getCartQuery($customerQuoteMaskedId),
            [],
            '',
            $this->getHeaderMap()
        );
    }

    /**
     * Test for assigning customer to non existent cart
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testAssigningCustomerToNonExistentCart(): void
    {
        $guestQuoteMaskedId = "non_existent_masked_id";
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Could not find a cart with ID \"{$guestQuoteMaskedId}\"");

        $this->graphQlMutation(
            $this->getAssignCustomerToGuestCartMutation($guestQuoteMaskedId),
            [],
            '',
            $this->getHeaderMap()
        );
    }

    /**
     * Test for assigning customer to the customer cart
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testAssignCustomerToCustomerCart(): void
    {
        $customerQuote = $this->getQuoteByReservedOrderId->execute('test_quote');
        $customerQuoteMaskedId = $this->quoteIdToMaskedId->execute((int)$customerQuote->getId());

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            "The current user cannot perform operations on cart \"{$customerQuoteMaskedId}\""
        );

        $this->graphQlMutation(
            $this->getAssignCustomerToGuestCartMutation($customerQuoteMaskedId),
            [],
            '',
            $this->getHeaderMap()
        );
    }

    /**
     * Create the assignCustomerToGuestCart mutation
     *
     * @param string $guestQuoteMaskedId
     * @return string
     */
    private function getAssignCustomerToGuestCartMutation(string $guestQuoteMaskedId): string
    {
        return <<<QUERY
mutation {
  assignCustomerToGuestCart(
    cart_id: "{$guestQuoteMaskedId}"
  ){
  items {
      quantity
      product {
        sku
      }
    }
  }
}
QUERY;
    }

    /**
     * Get cart query
     *
     * @param string $maskedId
     * @return string
     */
    private function getCartQuery(string $maskedId): string
    {
        return <<<QUERY
{
  cart(cart_id: "{$maskedId}") {
    items {
      quantity
      product {
        sku
      }
    }
  }
}
QUERY;
    }

    /**
     * Retrieve customer authorization headers
     *
     * @param string $username
     * @param string $password
     * @return array
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    private function getHeaderMap(string $username = 'customer@example.com', string $password = 'password'): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($username, $password);
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];
        return $headerMap;
    }
}
