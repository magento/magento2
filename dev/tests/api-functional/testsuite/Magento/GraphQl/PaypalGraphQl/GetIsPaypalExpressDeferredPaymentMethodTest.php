<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\PaypalGraphQl;

use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for getting cart information
 */
class GetIsPaypalExpressDeferredPaymentMethodTest extends GraphQlAbstract
{
    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
    }

    /**
     * @param string $maskedQuoteId
     * @return string
     */
    private function getQuery(string $maskedQuoteId): string
    {
        return <<<QUERY
        {
        cart(cart_id: "$maskedQuoteId") {
            available_payment_methods {
                code
                title
                is_deferred
            }
          }
        }
        QUERY;
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoConfigFixture default_store payment/paypal_billing_agreement/active 0
     * @magentoConfigFixture default_store payment/checkmo/active 0
     * @magentoConfigFixture default_store payment/cashondelivery/active 0
     * @magentoConfigFixture default_store payment/banktransfer/active 0
     * @magentoConfigFixture default_store payment/free/active 0
     * @magentoConfigFixture default_store payment/paypal_express/active 1
     */
    public function testGetIsPaypalExpressDeferredPaymentMethod()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query);

        self::assertArrayHasKey('cart', $response);
        self::assertArrayHasKey('available_payment_methods', $response['cart']);
        self::assertEquals('paypal_express', $response['cart']['available_payment_methods'][0]['code']);
        self::assertEquals('PayPal Express Checkout', $response['cart']['available_payment_methods'][0]['title']);
        self::assertEquals(true, $response['cart']['available_payment_methods'][0]['is_deferred']);
    }
}
