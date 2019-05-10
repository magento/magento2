<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Paypal\Express;

use Magento\GraphQl\Quote\GetQuoteByReservedOrderId;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test that Paypal payment method get set properly
 */
class SetPaymentMethodOnGuestCartTest extends GraphQlAbstract
{
    /**
     * @var GetQuoteByReservedOrderId
     */
    private $getQuoteByReservedOrderId;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getQuoteByReservedOrderId = $objectManager->get(GetQuoteByReservedOrderId::class);
        $this->configurePaypalExpress();
    }
    /**
     * @magentoApiDataFixture Magento/Paypal/Fixtures/enable_paypal_express.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     */
    public function testPaymentInformationIsSetOnQuote()
    {
        $cart = $this->getQuoteByReservedOrderId->execute('test_quote');
        $cartId = $cart->getId();
        $payerId = 'fakePayerId';
        $token = 'fakeToken';
        $methodCode = 'paypal_express';

        $mutation = <<<MUTATION
mutation {
  setPaymentMethodOnCart(input: {
    payment_method: {
      code: "$methodCode",
      additional_data: {
        $methodCode: {
          paypal_express_checkout_payer_id: "$payerId"
          paypal_express_checkout_token: "$token"
        }
      }
    },
    cart_id: "$cartId"
  }){
    cart{
      selected_payment_method{
        code
      }
    }
  }
}
MUTATION;

        $result = $this->graphQlMutation($mutation);
    }

}