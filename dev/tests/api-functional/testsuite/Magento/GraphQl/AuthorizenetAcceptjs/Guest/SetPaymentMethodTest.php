<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\AuthorizenetAcceptjs\Guest;

use Magento\Framework\Registry;
use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test setting payment method and placing order with AuthorizenetAcceptjs
 */
class SetPaymentMethodTest extends GraphQlAbstract
{
    private const VALID_DESCRIPTOR = 'COMMON.ACCEPT.INAPP.PAYMENT';
    private const VALID_NONCE = 'fake-nonce';

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
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
        $this->orderCollectionFactory = $objectManager->get(CollectionFactory::class);
        $this->orderRepository = $objectManager->get(OrderRepositoryInterface::class);
        $this->registry = Bootstrap::getObjectManager()->get(Registry::class);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/set_guest_email.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_billing_address.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_flatrate_shipping_method.php
     * @magentoApiDataFixture Magento/GraphQl/AuthorizenetAcceptjs/_files/enable_authorizenetacceptjs.php
     * @magentoConfigFixture default_store carriers/flatrate/active 1
     * @magentoConfigFixture default_store carriers/tablerate/active 1
     * @magentoConfigFixture default_store carriers/freeshipping/active 1
     * @param string $nonce
     * @param string $descriptor
     * @param bool $expectSuccess
     * @dataProvider dataProviderTestPlaceOrder
     */
    public function testPlaceOrder(string $nonce, string $descriptor, bool $expectSuccess)
    {
        $reservedOrderId = 'test_quote';
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute($reservedOrderId);

        $setPaymentMutation = $this->getSetPaymentMutation($maskedQuoteId, $descriptor, $nonce);
        $setPaymentResponse = $this->graphQlMutation($setPaymentMutation);

        $this->assertSetPaymentMethodResponse($setPaymentResponse, 'authorizenet_acceptjs');

        $placeOrderQuery = $this->getPlaceOrderMutation($maskedQuoteId);

        if (!$expectSuccess) {
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('Transaction has been declined. Please try again later.');
        }

        $placeOrderResponse = $this->graphQlMutation($placeOrderQuery);

        $this->assertPlaceOrderResponse($placeOrderResponse, $reservedOrderId);
    }

    public function dataProviderTestPlaceOrder(): array
    {
        return [
            [static::VALID_NONCE, static::VALID_DESCRIPTOR, true],
            ['nonce', static::VALID_DESCRIPTOR, false],
            [static::VALID_NONCE, 'descriptor', false],
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
     * Create setPaymentMethodOnCart mutation
     *
     * @param string $maskedQuoteId
     * @param string $descriptor
     * @param string $nonce
     * @return string
     */
    private function getSetPaymentMutation(string $maskedQuoteId, string $descriptor, string $nonce): string
    {
        return <<<QUERY
mutation {
  setPaymentMethodOnCart(input:{
    cart_id:"{$maskedQuoteId}"
    payment_method:{
      code:"authorizenet_acceptjs"
      authorizenet_acceptjs:{
        opaque_data_descriptor: "{$descriptor}"
        opaque_data_value: "{$nonce}"
        cc_last_4: 1111
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
     * Create placeOrder mutation
     *
     * @param string $maskedQuoteId
     * @return string
     */
    private function getPlaceOrderMutation(string $maskedQuoteId): string
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
    public function tearDown()
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
