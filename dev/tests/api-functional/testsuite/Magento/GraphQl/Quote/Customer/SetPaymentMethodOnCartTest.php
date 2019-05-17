<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Customer;

use Exception;
use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\OfflinePayments\Model\Cashondelivery;
use Magento\OfflinePayments\Model\Checkmo;
use Magento\OfflinePayments\Model\Purchaseorder;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for setting payment methods on cart by customer
 */
class SetPaymentMethodOnCartTest extends GraphQlAbstract
{
    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     */
    public function testSetPaymentOnCartWithSimpleProduct()
    {
        $methodCode = Checkmo::PAYMENT_METHOD_CHECKMO_CODE;
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = $this->getQuery($maskedQuoteId, $methodCode);
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());

        self::assertArrayHasKey('setPaymentMethodOnCart', $response);
        self::assertArrayHasKey('cart', $response['setPaymentMethodOnCart']);
        self::assertArrayHasKey('selected_payment_method', $response['setPaymentMethodOnCart']['cart']);
        self::assertEquals($methodCode, $response['setPaymentMethodOnCart']['cart']['selected_payment_method']['code']);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
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
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_virtual.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_virtual_product.php
     */
    public function testSetPaymentOnCartWithVirtualProduct()
    {
        $methodCode = Checkmo::PAYMENT_METHOD_CHECKMO_CODE;
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = $this->getQuery($maskedQuoteId, $methodCode);
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());

        self::assertArrayHasKey('setPaymentMethodOnCart', $response);
        self::assertArrayHasKey('cart', $response['setPaymentMethodOnCart']);
        self::assertArrayHasKey('selected_payment_method', $response['setPaymentMethodOnCart']['cart']);
        self::assertEquals($methodCode, $response['setPaymentMethodOnCart']['cart']['selected_payment_method']['code']);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
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
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     *
     * @expectedException Exception
     * @expectedExceptionMessage Could not find a cart with ID "non_existent_masked_id"
     */
    public function testSetPaymentOnNonExistentCart()
    {
        $maskedQuoteId = 'non_existent_masked_id';
        $methodCode = Checkmo::PAYMENT_METHOD_CHECKMO_CODE;

        $query = $this->getQuery($maskedQuoteId, $methodCode);
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * _security
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testSetPaymentMethodToGuestCart()
    {
        $methodCode = Checkmo::PAYMENT_METHOD_CHECKMO_CODE;
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = $this->getQuery($maskedQuoteId, $methodCode);

        $this->expectExceptionMessage(
            "The current user cannot perform operations on cart \"$maskedQuoteId\""
        );
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * _security
     * @magentoApiDataFixture Magento/Customer/_files/three_customers.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testSetPaymentMethodToAnotherCustomerCart()
    {
        $methodCode = Checkmo::PAYMENT_METHOD_CHECKMO_CODE;
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = $this->getQuery($maskedQuoteId, $methodCode);

        $this->expectExceptionMessage(
            "The current user cannot perform operations on cart \"$maskedQuoteId\""
        );
        $this->graphQlMutation($query, [], '', $this->getHeaderMap('customer2@search.example.com'));
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     *
     * @param string $input
     * @param string $message
     * @throws Exception
     * @dataProvider dataProviderSetPaymentMethodWithoutRequiredParameters
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
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
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
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
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
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/enable_offline_payment_methods.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_checkmo_payment_method.php
     */
    public function testReSetPayment()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $methodCode = Cashondelivery::PAYMENT_METHOD_CASHONDELIVERY_CODE;
        $query = $this->getQuery($maskedQuoteId, $methodCode);
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());

        self::assertArrayHasKey('setPaymentMethodOnCart', $response);
        self::assertArrayHasKey('cart', $response['setPaymentMethodOnCart']);
        self::assertArrayHasKey('selected_payment_method', $response['setPaymentMethodOnCart']['cart']);
        self::assertArrayHasKey('code', $response['setPaymentMethodOnCart']['cart']['selected_payment_method']);
        self::assertEquals($methodCode, $response['setPaymentMethodOnCart']['cart']['selected_payment_method']['code']);
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
