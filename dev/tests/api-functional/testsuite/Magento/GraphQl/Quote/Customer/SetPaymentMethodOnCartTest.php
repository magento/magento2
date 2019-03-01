<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Customer;

use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\OfflinePayments\Model\Checkmo;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for setting payment methods on cart by customer
 */
class SetPaymentMethodOnCartTest extends GraphQlAbstract
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

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_virtual_product_and_address.php
     */
    public function testSetPaymentWithVirtualProduct()
    {
        $methodCode = Checkmo::PAYMENT_METHOD_CHECKMO_CODE;
        $maskedQuoteId = $this->getMaskedQuoteIdByReversedQuoteId('test_order_with_virtual_product');

        $query = $this->prepareMutationQuery($maskedQuoteId, $methodCode);
        $response = $this->graphQlQuery($query, [], '', $this->getHeaderMap());

        self::assertArrayHasKey('setPaymentMethodOnCart', $response);
        self::assertArrayHasKey('cart', $response['setPaymentMethodOnCart']);
        self::assertArrayHasKey('selected_payment_method', $response['setPaymentMethodOnCart']['cart']);
        self::assertEquals($methodCode, $response['setPaymentMethodOnCart']['cart']['selected_payment_method']['code']);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     */
    public function testSetPaymentWithSimpleProduct()
    {
        $methodCode = Checkmo::PAYMENT_METHOD_CHECKMO_CODE;
        $maskedQuoteId = $this->getMaskedQuoteIdByReversedQuoteId('test_order_1');

        $query = $this->prepareMutationQuery($maskedQuoteId, $methodCode);
        $response = $this->graphQlQuery($query, [], '', $this->getHeaderMap());

        self::assertArrayHasKey('setPaymentMethodOnCart', $response);
        self::assertArrayHasKey('cart', $response['setPaymentMethodOnCart']);
        self::assertArrayHasKey('selected_payment_method', $response['setPaymentMethodOnCart']['cart']);
        self::assertEquals($methodCode, $response['setPaymentMethodOnCart']['cart']['selected_payment_method']['code']);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @expectedException \Exception
     * @expectedExceptionMessage The shipping address is missing. Set the address and try again.
     */
    public function testSetPaymentWithSimpleProductWithoutAddress()
    {
        $methodCode = Checkmo::PAYMENT_METHOD_CHECKMO_CODE;
        $maskedQuoteId = $this->assignQuoteToCustomer('test_order_with_simple_product_without_address', 1);

        $query = $this->prepareMutationQuery($maskedQuoteId, $methodCode);
        $this->graphQlQuery($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     * @expectedException \Exception
     * @expectedExceptionMessage The requested Payment Method is not available.
     */
    public function testSetNonExistingPaymentMethod()
    {
        $methodCode = 'noway';
        $maskedQuoteId = $this->getMaskedQuoteIdByReversedQuoteId('test_order_1');

        $query = $this->prepareMutationQuery($maskedQuoteId, $methodCode);
        $this->graphQlQuery($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     */
    public function testSetPaymentMethodToGuestCart()
    {
        $methodCode = Checkmo::PAYMENT_METHOD_CHECKMO_CODE;
        $maskedQuoteId = $this->getMaskedQuoteIdByReversedQuoteId('test_order_with_simple_product_without_address');

        $query = $this->prepareMutationQuery($maskedQuoteId, $methodCode);

        $this->expectExceptionMessage(
            "The current user cannot perform operations on cart \"$maskedQuoteId\""
        );

        $this->graphQlQuery($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/three_customers.php
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     */
    public function testSetPaymentMethodToAnotherCustomerCart()
    {
        $methodCode = Checkmo::PAYMENT_METHOD_CHECKMO_CODE;
        $maskedQuoteId = $this->getMaskedQuoteIdByReversedQuoteId('test_order_1');

        $query = $this->prepareMutationQuery($maskedQuoteId, $methodCode);

        $this->expectExceptionMessage(
            "The current user cannot perform operations on cart \"$maskedQuoteId\""
        );

        $this->graphQlQuery($query, [], '', $this->getHeaderMap('customer2@search.example.com'));
    }

    /**
     * @param string $maskedQuoteId
     * @param string $methodCode
     * @return string
     */
    private function prepareMutationQuery(
        string $maskedQuoteId,
        string $methodCode
    ) : string {
        return <<<QUERY
mutation {
  setPaymentMethodOnCart(input: {
      cart_id: "$maskedQuoteId"
      payment_method: {
          code: "$methodCode"
      }
  }) {    
    cart {
      selected_payment_method {
        code
      }
    }
  }
}
QUERY;
    }

    /**
     * @param string $reversedQuoteId
     * @return string
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
     */
    private function getHeaderMap(string $username = 'customer@example.com', string $password = 'password'): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($username, $password);
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];
        return $headerMap;
    }
}
