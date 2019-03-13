<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Guest;

use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for getting cart information
 */
class GetAvailablePaymentMethodsTest extends GraphQlAbstract
{
    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     */
    public function testGetCartWithPaymentMethods()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute(
            'test_order_with_simple_product_without_address'
        );
        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query);

        self::assertArrayHasKey('cart', $response);

        self::assertEquals('checkmo', $response['cart']['available_payment_methods'][0]['code']);
        self::assertEquals('Check / Money order', $response['cart']['available_payment_methods'][0]['title']);

        self::assertEquals('free', $response['cart']['available_payment_methods'][1]['code']);
        self::assertEquals(
            'No Payment Information Required',
            $response['cart']['available_payment_methods'][1]['title']
        );
        self::assertGreaterThan(
            0,
            count($response['cart']['available_payment_methods']),
            'There are no available payment methods for guest cart!'
        );
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     */
    public function testGetPaymentMethodsFromCustomerCart()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');
        $query = $this->getQuery($maskedQuoteId);

        $this->expectExceptionMessage(
            "The current user cannot perform operations on cart \"$maskedQuoteId\""
        );
        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @magentoApiDataFixture Magento/Payment/_files/disable_all_active_payment_methods.php
     */
    public function testGetPaymentMethodsIfPaymentsAreNotSet()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute(
            'test_order_with_simple_product_without_address'
        );
        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query);

        self::assertEquals(0, count($response['cart']['available_payment_methods']));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Could not find a cart with ID "non_existent_masked_id"
     */
    public function testGetPaymentMethodsOfNonExistentCart()
    {
        $maskedQuoteId = 'non_existent_masked_id';
        $query = $this->getQuery($maskedQuoteId);
        $this->graphQlQuery($query);
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
    available_payment_methods {
      code
      title
    }
  }
}
QUERY;
    }
}
