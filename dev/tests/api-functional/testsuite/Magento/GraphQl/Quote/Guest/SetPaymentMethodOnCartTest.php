<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Guest;

use Magento\OfflinePayments\Model\Checkmo;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\QuoteGraphQl\Model\GetMaskedQuoteIdByReversedQuoteId;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for setting payment methods on cart by guest
 */
class SetPaymentMethodOnCartTest extends GraphQlAbstract
{
    /**
     * @var GetMaskedQuoteIdByReversedQuoteId
     */
    private $getMaskedQuoteIdByReversedQuoteId;

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
        $this->getMaskedQuoteIdByReversedQuoteId = $objectManager->get(GetMaskedQuoteIdByReversedQuoteId::class);
        $this->quoteResource = $objectManager->get(QuoteResource::class);
        $this->quoteFactory = $objectManager->get(QuoteFactory::class);
        $this->quoteIdToMaskedId = $objectManager->get(QuoteIdToMaskedQuoteIdInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_virtual_product_and_address.php
     * @throws \Exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testSetPaymentWithVirtualProduct()
    {
        $methodCode = Checkmo::PAYMENT_METHOD_CHECKMO_CODE;
        $maskedQuoteId = $this->getMaskedQuoteIdByReversedQuoteId->execute('test_order_with_virtual_product');
        $this->unAssignCustomerFromQuote('test_order_with_virtual_product');

        $query = $this->prepareMutationQuery($maskedQuoteId, $methodCode);
        $response = $this->graphQlQuery($query);

        self::assertArrayHasKey('setPaymentMethodOnCart', $response);
        self::assertArrayHasKey('cart', $response['setPaymentMethodOnCart']);
        self::assertArrayHasKey('selected_payment_method', $response['setPaymentMethodOnCart']['cart']);
        self::assertEquals($methodCode, $response['setPaymentMethodOnCart']['cart']['selected_payment_method']['code']);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     * @throws \Exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testSetPaymentWithSimpleProduct()
    {
        $methodCode = Checkmo::PAYMENT_METHOD_CHECKMO_CODE;
        $maskedQuoteId = $this->getMaskedQuoteIdByReversedQuoteId->execute('test_order_1');
        $this->unAssignCustomerFromQuote('test_order_1');

        $query = $this->prepareMutationQuery($maskedQuoteId, $methodCode);
        $response = $this->graphQlQuery($query);

        self::assertArrayHasKey('setPaymentMethodOnCart', $response);
        self::assertArrayHasKey('cart', $response['setPaymentMethodOnCart']);
        self::assertArrayHasKey('selected_payment_method', $response['setPaymentMethodOnCart']['cart']);
        self::assertEquals($methodCode, $response['setPaymentMethodOnCart']['cart']['selected_payment_method']['code']);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @expectedException \Exception
     * @expectedExceptionMessage The shipping address is missing. Set the address and try again.
     */
    public function testSetPaymentWithSimpleProductWithoutAddress()
    {
        $methodCode = Checkmo::PAYMENT_METHOD_CHECKMO_CODE;
        $maskedQuoteId = $this->getMaskedQuoteIdByReversedQuoteId->execute('test_order_with_simple_product_without_address');

        $query = $this->prepareMutationQuery($maskedQuoteId, $methodCode);
        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     * @expectedException \Exception
     * @expectedExceptionMessage The requested Payment Method is not available.
     */
    public function testSetNonExistingPaymentMethod()
    {
        $methodCode = 'noway';
        $maskedQuoteId = $this->getMaskedQuoteIdByReversedQuoteId->execute('test_order_1');
        $this->unAssignCustomerFromQuote('test_order_1');

        $query = $this->prepareMutationQuery($maskedQuoteId, $methodCode);
        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     * @throws \Exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testSetPaymentMethodToCustomerCart()
    {
        $methodCode = Checkmo::PAYMENT_METHOD_CHECKMO_CODE;
        $maskedQuoteId = $this->getMaskedQuoteIdByReversedQuoteId->execute('test_order_1');

        $query = $this->prepareMutationQuery($maskedQuoteId, $methodCode);

        $this->expectExceptionMessage(
            "The current user cannot perform operations on cart \"$maskedQuoteId\""
        );
        $this->graphQlQuery($query);
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
  setPaymentMethodOnCart(input: 
    {
      cart_id: "{$maskedQuoteId}", 
      payment_method: {
          code: "{$methodCode}"
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
     * @param int $customerId
     * @return string
     */
    private function unAssignCustomerFromQuote(
        string $reversedQuoteId
    ): string {
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, $reversedQuoteId, 'reserved_order_id');
        $quote->setCustomerId(0);
        $this->quoteResource->save($quote);
        return $this->quoteIdToMaskedId->execute((int)$quote->getId());
    }
}
