<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Braintree\Customer;

use Magento\Braintree\Gateway\Command\GetPaymentNonceCommand;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Registry;
use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Vault\Model\ResourceModel\PaymentToken;
use Magento\Vault\Model\ResourceModel\PaymentToken\CollectionFactory as TokenCollectionFactory;

/**
 * Test setting payment method and placing order with Braintree
 */
class SetPaymentMethodTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    /**
     * @var CollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var TokenCollectionFactory
     */
    private $tokenCollectionFactory;

    /**
     * @var PaymentToken
     */
    private $tokenResource;

    /**
     * @var GetPaymentNonceCommand
     */
    private $getNonceCommand;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
        $this->orderCollectionFactory = $objectManager->get(CollectionFactory::class);
        $this->orderRepository = $objectManager->get(OrderRepositoryInterface::class);
        $this->registry = Bootstrap::getObjectManager()->get(Registry::class);
        $this->tokenCollectionFactory = Bootstrap::getObjectManager()->get(TokenCollectionFactory::class);
        $this->tokenResource = Bootstrap::getObjectManager()->get(PaymentToken::class);
        $this->getNonceCommand = Bootstrap::getObjectManager()->get(GetPaymentNonceCommand::class);
    }

    /**
     * @magentoConfigFixture default_store carriers/flatrate/active 1
     * @magentoConfigFixture default_store payment/braintree/active 1
     * @magentoConfigFixture default_store payment/braintree/environment sandbox
     * @magentoConfigFixture default_store payment/braintree/merchant_id def_merchant_id
     * @magentoConfigFixture default_store payment/braintree/public_key def_public_key
     * @magentoConfigFixture default_store payment/braintree/private_key def_private_key
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_billing_address.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_flatrate_shipping_method.php
     */
    public function testPlaceOrder()
    {
        $reservedOrderId = 'test_quote';
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute($reservedOrderId);

        $setPaymentQuery = $this->getSetPaymentBraintreeQuery($maskedQuoteId);
        $setPaymentResponse = $this->graphQlMutation($setPaymentQuery, [], '', $this->getHeaderMap());

        $this->assertSetPaymentMethodResponse($setPaymentResponse, 'braintree');

        $placeOrderQuery = $this->getPlaceOrderQuery($maskedQuoteId);
        $placeOrderResponse = $this->graphQlMutation($placeOrderQuery, [], '', $this->getHeaderMap());

        $this->assertPlaceOrderResponse($placeOrderResponse, $reservedOrderId);

        $tokenQueryResult = $this->graphQlQuery($this->getPaymentTokenQuery(), [], '', $this->getHeaderMap());

        self::assertArrayHasKey('customerPaymentTokens', $tokenQueryResult);
        self::assertArrayHasKey('items', $tokenQueryResult['customerPaymentTokens']);
        self::assertCount(0, $tokenQueryResult['customerPaymentTokens']['items']);
    }

    /**
     * @magentoConfigFixture default_store carriers/flatrate/active 1
     * @magentoConfigFixture default_store payment/braintree/active 1
     * @magentoConfigFixture default_store payment/braintree_cc_vault/active 1
     * @magentoConfigFixture default_store payment/braintree/environment sandbox
     * @magentoConfigFixture default_store payment/braintree/merchant_id def_merchant_id
     * @magentoConfigFixture default_store payment/braintree/public_key def_public_key
     * @magentoConfigFixture default_store payment/braintree/private_key def_private_key
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_billing_address.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_flatrate_shipping_method.php
     */
    public function testPlaceOrderSaveInVault()
    {
        $reservedOrderId = 'test_quote';
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute($reservedOrderId);

        $setPaymentQuery = $this->getSetPaymentBraintreeQuery($maskedQuoteId, true);
        $setPaymentResponse = $this->graphQlMutation($setPaymentQuery, [], '', $this->getHeaderMap());

        $this->assertSetPaymentMethodResponse($setPaymentResponse, 'braintree');

        $placeOrderQuery = $this->getPlaceOrderQuery($maskedQuoteId);
        $placeOrderResponse = $this->graphQlMutation($placeOrderQuery, [], '', $this->getHeaderMap());

        $this->assertPlaceOrderResponse($placeOrderResponse, $reservedOrderId);

        $tokenQueryResult = $this->graphQlQuery($this->getPaymentTokenQuery(), [], '', $this->getHeaderMap());

        self::assertArrayHasKey('customerPaymentTokens', $tokenQueryResult);
        self::assertArrayHasKey('items', $tokenQueryResult['customerPaymentTokens']);
        self::assertCount(1, $tokenQueryResult['customerPaymentTokens']['items']);
        $token = current($tokenQueryResult['customerPaymentTokens']['items']);
        self::assertArrayHasKey('payment_method_code', $token);
        self::assertEquals('braintree', $token['payment_method_code']);
        self::assertArrayHasKey('type', $token);
        self::assertEquals('card', $token['type']);
        self::assertArrayHasKey('details', $token);
        self::assertArrayHasKey('public_hash', $token);
    }

    /**
     * @magentoConfigFixture default_store carriers/flatrate/active 1
     * @magentoConfigFixture default_store payment/braintree/active 1
     * @magentoConfigFixture default_store payment/braintree_cc_vault/active 1
     * @magentoConfigFixture default_store payment/braintree/environment sandbox
     * @magentoConfigFixture default_store payment/braintree/merchant_id def_merchant_id
     * @magentoConfigFixture default_store payment/braintree/public_key def_public_key
     * @magentoConfigFixture default_store payment/braintree/private_key def_private_key
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_billing_address.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_flatrate_shipping_method.php
     * @magentoApiDataFixture Magento/GraphQl/Braintree/_files/token.php
     */
    public function testPlaceOrderWithVault()
    {
        $reservedOrderId = 'test_quote';
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute($reservedOrderId);

        $setPaymentQuery = $this->getSetPaymentBraintreeVaultQuery(
            $maskedQuoteId,
            'braintree_public_hash'
        );
        $setPaymentResponse = $this->graphQlMutation($setPaymentQuery, [], '', $this->getHeaderMap());

        $this->assertSetPaymentMethodResponse($setPaymentResponse, 'braintree_cc_vault');

        $placeOrderQuery = $this->getPlaceOrderQuery($maskedQuoteId);
        $placeOrderResponse = $this->graphQlMutation($placeOrderQuery, [], '', $this->getHeaderMap());

        $this->assertPlaceOrderResponse($placeOrderResponse, $reservedOrderId);
    }

    /**
     * @magentoConfigFixture default_store carriers/flatrate/active 1
     * @magentoConfigFixture default_store payment/braintree/active 1
     * @magentoConfigFixture default_store payment/braintree_cc_vault/active 1
     * @magentoConfigFixture default_store payment/braintree/environment sandbox
     * @magentoConfigFixture default_store payment/braintree/merchant_id def_merchant_id
     * @magentoConfigFixture default_store payment/braintree/public_key def_public_key
     * @magentoConfigFixture default_store payment/braintree/private_key def_private_key
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_billing_address.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_flatrate_shipping_method.php
     * @dataProvider dataProviderTestSetPaymentMethodInvalidInput
     * @param string $methodCode
     */
    public function testSetPaymentMethodInvalidInput(string $methodCode)
    {
        $this->expectException(\Exception::class);

        $reservedOrderId = 'test_quote';
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute($reservedOrderId);

        $setPaymentQuery = $this->getSetPaymentBraintreeQueryInvalidInput(
            $maskedQuoteId,
            $methodCode
        );
        $this->expectExceptionMessage("Required parameter \"$methodCode\" for \"payment_method\" is missing.");
        $this->graphQlMutation($setPaymentQuery, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoConfigFixture default_store carriers/flatrate/active 1
     * @magentoConfigFixture default_store payment/braintree/active 1
     * @magentoConfigFixture default_store payment/braintree_cc_vault/active 1
     * @magentoConfigFixture default_store payment/braintree/environment sandbox
     * @magentoConfigFixture default_store payment/braintree/merchant_id def_merchant_id
     * @magentoConfigFixture default_store payment/braintree/public_key def_public_key
     * @magentoConfigFixture default_store payment/braintree/private_key def_private_key
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_billing_address.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_flatrate_shipping_method.php
     * @dataProvider dataProviderTestSetPaymentMethodInvalidInput
     * @param string $methodCode
     */
    public function testSetPaymentMethodInvalidMethodInput(string $methodCode)
    {
        $this->expectException(\Exception::class);

        $reservedOrderId = 'test_quote';
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute($reservedOrderId);

        $setPaymentQuery = $this->getSetPaymentBraintreeQueryInvalidMethodInput(
            $maskedQuoteId,
            $methodCode
        );
        $this->expectExceptionMessage("for \"$methodCode\" is missing.");
        $expectedExceptionMessages = [
            'braintree' =>
                'Field BraintreeInput.is_active_payment_token_enabler of required type Boolean! was not provided.',
            'braintree_cc_vault' =>
                'Field BraintreeCcVaultInput.public_hash of required type String! was not provided.'
        ];

        $this->expectExceptionMessage($expectedExceptionMessages[$methodCode]);
        $this->graphQlMutation($setPaymentQuery, [], '', $this->getHeaderMap());
    }

    public function dataProviderTestSetPaymentMethodInvalidInput(): array
    {
        return [
            ['braintree'],
            ['braintree_cc_vault'],
        ];
    }

    private function assertPlaceOrderResponse(array $response, string $reservedOrderId): void
    {
        self::assertArrayHasKey('placeOrder', $response);
        self::assertArrayHasKey('order', $response['placeOrder']);
        self::assertArrayHasKey('order_number', $response['placeOrder']['order']);
        self::assertEquals($reservedOrderId, $response['placeOrder']['order']['order_number']);
    }

    private function assertSetPaymentMethodResponse(array $response, string $methodCode): void
    {
        self::assertArrayHasKey('setPaymentMethodOnCart', $response);
        self::assertArrayHasKey('cart', $response['setPaymentMethodOnCart']);
        self::assertArrayHasKey('selected_payment_method', $response['setPaymentMethodOnCart']['cart']);
        self::assertArrayHasKey('code', $response['setPaymentMethodOnCart']['cart']['selected_payment_method']);
        self::assertEquals($methodCode, $response['setPaymentMethodOnCart']['cart']['selected_payment_method']['code']);
    }

    /**
     * @param string $maskedQuoteId
     * @param bool $saveInVault
     * @return string
     */
    private function getSetPaymentBraintreeQuery(string $maskedQuoteId, bool $saveInVault = false): string
    {
        $saveInVault = json_encode($saveInVault);
        return <<<QUERY
mutation {
  setPaymentMethodOnCart(input:{
    cart_id:"{$maskedQuoteId}"
    payment_method:{
      code:"braintree"
      braintree:{
        is_active_payment_token_enabler:{$saveInVault}
        payment_method_nonce:"fake-valid-nonce"
      }
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
     * @param string $maskedQuoteId
     * @param string $publicHash
     * @return string
     */
    private function getSetPaymentBraintreeVaultQuery(
        string $maskedQuoteId,
        string $publicHash
    ): string {
        return <<<QUERY
mutation {
  setPaymentMethodOnCart(input:{
    cart_id:"{$maskedQuoteId}"
    payment_method:{
      code:"braintree_cc_vault"
      braintree_cc_vault:{
        public_hash:"{$publicHash}"
      }
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
     * @param string $maskedQuoteId
     * @param string $methodCode
     * @return string
     */
    private function getSetPaymentBraintreeQueryInvalidInput(string $maskedQuoteId, string $methodCode): string
    {
        return <<<QUERY
mutation {
  setPaymentMethodOnCart(input:{
    cart_id:"{$maskedQuoteId}"
    payment_method:{
      code:"{$methodCode}"
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
     * @param string $maskedQuoteId
     * @param string $methodCode
     * @return string
     */
    private function getSetPaymentBraintreeQueryInvalidMethodInput(string $maskedQuoteId, string $methodCode): string
    {
        return <<<QUERY
mutation {
  setPaymentMethodOnCart(input:{
    cart_id:"{$maskedQuoteId}"
    payment_method:{
      code:"{$methodCode}"
      {$methodCode}: {}
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
     * @param string $maskedQuoteId
     * @return string
     */
    private function getPlaceOrderQuery(string $maskedQuoteId): string
    {
        return <<<QUERY
mutation {
  placeOrder(input: {cart_id: "{$maskedQuoteId}"}) {
    order {
      order_number
    }
  }
}
QUERY;
    }

    /**
     * @return string
     */
    private function getPaymentTokenQuery(): string
    {
        return <<<QUERY
query {
  customerPaymentTokens {
    items {
      details
      payment_method_code
      public_hash
      type
    }
  }
}
QUERY;
    }

    /**
     * @param string $username
     * @param string $password
     * @return array
     * @throws AuthenticationException
     */
    private function getHeaderMap(string $username = 'customer@example.com', string $password = 'password'): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($username, $password);
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];
        return $headerMap;
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);

        $orderCollection = $this->orderCollectionFactory->create();
        foreach ($orderCollection as $order) {
            $this->orderRepository->delete($order);
        }

        $tokenCollection = $this->tokenCollectionFactory->create();
        foreach ($tokenCollection as $token) {
            $this->tokenResource->delete($token);
        }
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', false);

        parent::tearDown();
    }
}
