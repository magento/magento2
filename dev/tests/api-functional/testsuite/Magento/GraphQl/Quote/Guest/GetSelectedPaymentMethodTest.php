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
 * Test for getting selected payment method from cart
 */
class GetSelectedPaymentMethodTest extends GraphQlAbstract
{
    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoConfigFixture default_store payment/banktransfer/active 1
     * @magentoConfigFixture default_store payment/cashondelivery/active 1
     * @magentoConfigFixture default_store payment/checkmo/active 1
     * @magentoConfigFixture default_store payment/purchaseorder/active 1
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_checkmo_payment_method.php
     *
     */
    public function testGetSelectedPaymentMethod()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey('cart', $response);
        $this->assertArrayHasKey('selected_payment_method', $response['cart']);
        $this->assertArrayHasKey('code', $response['cart']['selected_payment_method']);
        $this->assertEquals('checkmo', $response['cart']['selected_payment_method']['code']);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoConfigFixture default_store payment/banktransfer/active 1
     * @magentoConfigFixture default_store payment/cashondelivery/active 1
     * @magentoConfigFixture default_store payment/checkmo/active 1
     * @magentoConfigFixture default_store payment/purchaseorder/active 1
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     */
    public function testGetSelectedPaymentMethodBeforeSet()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey('cart', $response);
        $this->assertArrayHasKey('selected_payment_method', $response['cart']);
        $this->assertArrayHasKey('code', $response['cart']['selected_payment_method']);
        $this->assertEquals('', $response['cart']['selected_payment_method']['code']);
    }

    /**
     */
    public function testGetSelectedPaymentMethodFromNonExistentCart()
    {
        $this->expectException(\Exception::class);

        $maskedQuoteId = 'non_existent_masked_id';
        $query = $this->getQuery($maskedQuoteId);

        $this->expectExceptionMessage(
            'Could not find a cart with ID "non_existent_masked_id"'
        );

        $this->graphQlQuery($query);
    }

    /**
     * _security
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoConfigFixture default_store payment/banktransfer/active 1
     * @magentoConfigFixture default_store payment/cashondelivery/active 1
     * @magentoConfigFixture default_store payment/checkmo/active 1
     * @magentoConfigFixture default_store payment/purchaseorder/active 1
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_checkmo_payment_method.php
     */
    public function testGetSelectedPaymentMethodFromCustomerCart()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId);

        $this->expectExceptionMessage(
            "The current user cannot perform operations on cart \"{$maskedQuoteId}\""
        );

        $this->graphQlQuery($query);
    }

    /**
     * @param string $maskedQuoteId
     * @return string
     */
    private function getQuery(string $maskedQuoteId): string
    {
        return <<<QUERY
{
  cart(cart_id:"$maskedQuoteId") {
    selected_payment_method {
      code
    }
  }
}
QUERY;
    }
}
