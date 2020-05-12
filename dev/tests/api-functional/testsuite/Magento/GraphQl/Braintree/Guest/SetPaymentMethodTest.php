<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Braintree\Guest;

use Magento\Framework\Registry;
use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

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
    }

    /**
     * @magentoConfigFixture default_store carriers/flatrate/active 1
     * @magentoConfigFixture default_store payment/braintree/active 1
     * @magentoConfigFixture default_store payment/braintree/environment sandbox
     * @magentoConfigFixture default_store payment/braintree/merchant_id def_merchant_id
     * @magentoConfigFixture default_store payment/braintree/public_key def_public_key
     * @magentoConfigFixture default_store payment/braintree/private_key def_private_key
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/set_guest_email.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_billing_address.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_flatrate_shipping_method.php
     * @dataProvider dataProviderTestPlaceOrder
     */
    public function testPlaceOrder(string $nonce)
    {
        $reservedOrderId = 'test_quote';
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute($reservedOrderId);

        $setPaymentQuery = $this->getSetPaymentBraintreeQuery($maskedQuoteId, $nonce);
        $setPaymentResponse = $this->graphQlMutation($setPaymentQuery);

        $this->assertSetPaymentMethodResponse($setPaymentResponse, 'braintree');

        $placeOrderQuery = $this->getPlaceOrderQuery($maskedQuoteId);
        $placeOrderResponse = $this->graphQlMutation($placeOrderQuery);

        $this->assertPlaceOrderResponse($placeOrderResponse, $reservedOrderId);
    }

    /**
     * Data provider for testPlaceOrder
     *
     * @return array
     */
    public function dataProviderTestPlaceOrder(): array
    {
        return [
            ['fake-valid-nonce'],
            ['fake-apple-pay-visa-nonce'],
        ];
    }

    /**
     * @magentoConfigFixture default_store carriers/flatrate/active 1
     * @magentoConfigFixture default_store payment/braintree/active 1
     * @magentoConfigFixture default_store payment/braintree/environment sandbox
     * @magentoConfigFixture default_store payment/braintree/merchant_id def_merchant_id
     * @magentoConfigFixture default_store payment/braintree/public_key def_public_key
     * @magentoConfigFixture default_store payment/braintree/private_key def_private_key
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/set_guest_email.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_billing_address.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_flatrate_shipping_method.php
     */
    public function testSetPaymentMethodInvalidInput()
    {
        $this->expectException(\Exception::class);

        $reservedOrderId = 'test_quote';
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute($reservedOrderId);

        $setPaymentQuery = $this->getSetPaymentBraintreeQueryInvalidInput($maskedQuoteId);
        $this->expectExceptionMessage("Required parameter \"braintree\" for \"payment_method\" is missing.");
        $this->graphQlMutation($setPaymentQuery);
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
     * @return string
     */
    private function getSetPaymentBraintreeQuery(string $maskedQuoteId, string $nonce): string
    {
        return <<<QUERY
mutation {
  setPaymentMethodOnCart(input:{
    cart_id:"{$maskedQuoteId}"
    payment_method:{
      code:"braintree"
      braintree:{
        is_active_payment_token_enabler:false
        payment_method_nonce:"{$nonce}"
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
     * @return string
     */
    private function getSetPaymentBraintreeQueryInvalidInput(string $maskedQuoteId): string
    {
        return <<<QUERY
mutation {
  setPaymentMethodOnCart(input:{
    cart_id:"{$maskedQuoteId}"
    payment_method:{
      code:"braintree"
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
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', false);

        parent::tearDown();
    }
}
