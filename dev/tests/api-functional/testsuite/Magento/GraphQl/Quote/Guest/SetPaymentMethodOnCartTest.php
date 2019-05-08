<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Guest;

use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\OfflinePayments\Model\Cashondelivery;
use Magento\OfflinePayments\Model\Checkmo;
use Magento\OfflinePayments\Model\Purchaseorder;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for setting payment methods on cart by guest
 */
class SetPaymentMethodOnCartTest extends GraphQlAbstract
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
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     */
    public function testSetPaymentOnCartWithSimpleProduct()
    {
        $methodCode = Checkmo::PAYMENT_METHOD_CHECKMO_CODE;
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = $this->getQuery($maskedQuoteId, $methodCode);
        $response = $this->graphQlMutation($query);

        self::assertArrayHasKey('setPaymentMethodOnCart', $response);
        self::assertArrayHasKey('cart', $response['setPaymentMethodOnCart']);
        self::assertArrayHasKey('selected_payment_method', $response['setPaymentMethodOnCart']['cart']);
        self::assertEquals($methodCode, $response['setPaymentMethodOnCart']['cart']['selected_payment_method']['code']);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     *
     * @expectedException Exception
     * @expectedExceptionMessage The shipping address is missing. Set the address and try again.
     */
    public function testSetPaymentOnCartWithSimpleProductAndWithoutAddress()
    {
        $methodCode = Checkmo::PAYMENT_METHOD_CHECKMO_CODE;
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = $this->getQuery($maskedQuoteId, $methodCode);
        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_virtual.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_virtual_product.php
     */
    public function testSetPaymentOnCartWithVirtualProduct()
    {
        $methodCode = Checkmo::PAYMENT_METHOD_CHECKMO_CODE;
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = $this->getQuery($maskedQuoteId, $methodCode);
        $response = $this->graphQlMutation($query);

        self::assertArrayHasKey('setPaymentMethodOnCart', $response);
        self::assertArrayHasKey('cart', $response['setPaymentMethodOnCart']);
        self::assertArrayHasKey('selected_payment_method', $response['setPaymentMethodOnCart']['cart']);
        self::assertEquals($methodCode, $response['setPaymentMethodOnCart']['cart']['selected_payment_method']['code']);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     *
     * @expectedException Exception
     * @expectedExceptionMessage The requested Payment Method is not available.
     */
    public function testSetNonExistentPaymentMethod()
    {
        $methodCode = 'noway';
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = $this->getQuery($maskedQuoteId, $methodCode);
        $this->graphQlMutation($query);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Could not find a cart with ID "non_existent_masked_id"
     */
    public function testSetPaymentOnNonExistentCart()
    {
        $maskedQuoteId = 'non_existent_masked_id';
        $methodCode = Checkmo::PAYMENT_METHOD_CHECKMO_CODE;

        $query = $this->getQuery($maskedQuoteId, $methodCode);
        $this->graphQlMutation($query);
    }

    /**
     * _security
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     */
    public function testSetPaymentMethodToCustomerCart()
    {
        $methodCode = Checkmo::PAYMENT_METHOD_CHECKMO_CODE;
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = $this->getQuery($maskedQuoteId, $methodCode);

        $this->expectExceptionMessage(
            "The current user cannot perform operations on cart \"$maskedQuoteId\""
        );
        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     *
     * @param string $input
     * @param string $message
     * @dataProvider dataProviderSetPaymentMethodWithoutRequiredParameters
     * @throws Exception
     */
    public function testSetPaymentMethodWithoutRequiredParameters(string $input, string $message)
    {
        $query = <<<QUERY
mutation {
  setPaymentMethodOnCart(
    input: {
      {$input}
    }
  ) {
    cart {
      items {
        quantity
      }
    }
  }
}
QUERY;
        $this->expectExceptionMessage($message);
        $this->graphQlMutation($query);
    }

    /**
     * @return array
     */
    public function dataProviderSetPaymentMethodWithoutRequiredParameters(): array
    {
        return [
            'missed_cart_id' => [
                'payment_method: {code: "' . Checkmo::PAYMENT_METHOD_CHECKMO_CODE . '"}',
                'Required parameter "cart_id" is missing.'
            ],
            'missed_payment_method' => [
                'cart_id: "test"',
                'Required parameter "code" for "payment_method" is missing.'
            ],
            'missed_payment_method_code' => [
                'cart_id: "test", payment_method: {code: ""}',
                'Required parameter "code" for "payment_method" is missing.'
            ],
        ];
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/enable_offline_payment_methods.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_checkmo_payment_method.php
     */
    public function testReSetPayment()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $methodCode = Cashondelivery::PAYMENT_METHOD_CASHONDELIVERY_CODE;
        $query = $this->getQuery($maskedQuoteId, $methodCode);
        $response = $this->graphQlMutation($query);

        self::assertArrayHasKey('setPaymentMethodOnCart', $response);
        self::assertArrayHasKey('cart', $response['setPaymentMethodOnCart']);
        self::assertArrayHasKey('selected_payment_method', $response['setPaymentMethodOnCart']['cart']);
        self::assertArrayHasKey('code', $response['setPaymentMethodOnCart']['cart']['selected_payment_method']);
        self::assertEquals($methodCode, $response['setPaymentMethodOnCart']['cart']['selected_payment_method']['code']);
    }

    /**
     *
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     */
    public function testSetPaymentMethodOnCartWithAuthorizenet()
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var \Magento\Config\Model\ResourceModel\Config $config */
        $config = $objectManager->get(\Magento\Config\Model\ResourceModel\Config::class);
        $config->saveConfig('payment/authorizenet_acceptjs/active',
            1, ScopeConfigInterface::SCOPE_TYPE_DEFAULT,0);
        $config->saveConfig('payment/authorizenet_acceptjs/environment',
                              'sandbox', ScopeConfigInterface::SCOPE_TYPE_DEFAULT,0);
        $config->saveConfig('payment/authorizenet_acceptjs/login',
            'someusername', ScopeConfigInterface::SCOPE_TYPE_DEFAULT,0);
        $config->saveConfig('payment/authorizenet_acceptjs/trans_key',
            'somepassword', ScopeConfigInterface::SCOPE_TYPE_DEFAULT,0);
        $config->saveConfig('payment/authorizenet_acceptjs/trans_signature_key',
            'abc', ScopeConfigInterface::SCOPE_TYPE_DEFAULT,0);
        $config->saveConfig('payment/authorizenet_acceptjs/public_client_key',
            'xyz', ScopeConfigInterface::SCOPE_TYPE_DEFAULT,0);
        //$config->rollBack();

        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $methodCode = 'authorizenet_acceptjs';
        $query =
            <<<QUERY
    mutation {
    setPaymentMethodOnCart(
        input: {
            cart_id: "{$maskedQuoteId}",
            payment_method: {
                code:"{$methodCode}",
                additional_data: {
                    authorizenet_acceptjs: {
                        opaque_data_descriptor:
                         "COMMON.ACCEPT.INAPP.PAYMENT",
                         opaque_data_value: "abx",
                         cc_last_4: 1111
                         }
                        }
                       }
                      }
                     ) {
                        cart {
                            selected_payment_method { 
                            code, 
                            additional_data { 
                                authorizenet_acceptjs { 
                                    cc_last_4,
                                    opaque_data_value,
                                    opaque_data_descriptor
                                    } } } items {product {sku}}}}}
QUERY;
        $response = $this->graphQlMutation($query);

        self::assertArrayHasKey('setPaymentMethodOnCart', $response);
        self::assertArrayHasKey('cart', $response['setPaymentMethodOnCart']);
        self::assertArrayHasKey('selected_payment_method', $response['setPaymentMethodOnCart']['cart']);
        $selectedPaymentMethod = $response['setPaymentMethodOnCart']['cart']['selected_payment_method'];
        self::assertArrayHasKey('code', $selectedPaymentMethod);
        self::assertArrayHasKey('additional_data', $selectedPaymentMethod);
        $additionalData = $selectedPaymentMethod['additional_data'];
        self::assertArrayHasKey('cc_last_4', $additionalData['authorizenet_acceptjs']);
        self::assertArrayHasKey('opaque_data_descriptor', $additionalData['authorizenet_acceptjs']);
        self::assertArrayHasKey('opaque_data_value', $additionalData['authorizenet_acceptjs']);
        self::assertEquals($methodCode, $selectedPaymentMethod['code']);
        self::assertEquals('1111', $additionalData['authorizenet_acceptjs']['cc_last_4']);
        self::assertEquals('abx', $additionalData['authorizenet_acceptjs']['opaque_data_value']);
        self::assertEquals(
            'COMMON.ACCEPT.INAPP.PAYMENT',
            $additionalData['authorizenet_acceptjs']['opaque_data_descriptor']
        );
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @expectedException Exception
     * @expectedExceptionMessage The requested Payment Method is not available.
     */
    public function testSetDisabledPaymentOnCart()
    {
        $methodCode = Purchaseorder::PAYMENT_METHOD_PURCHASEORDER_CODE;
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = $this->getQuery($maskedQuoteId, $methodCode);
        $this->graphQlMutation($query);
    }

    /**
     * @param string $maskedQuoteId
     * @param string $methodCode
     * @return string
     */
    private function getQuery(
        string $maskedQuoteId,
        string $methodCode
    ) : string {
        return <<<QUERY
mutation {
  setPaymentMethodOnCart(input: {
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
}
