<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AuthorizenetGraphQl\Model\Resolver\Customer;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\GraphQl\Service\GraphQlRequest;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zend_Http_Response;

/**
 * Tests end to end Place Order process for customer via authorizeNet
 *
 * @magentoAppArea graphql
 * @magentoDbIsolation disabled
 */
class PlaceOrderWithAuthorizeNetTest extends TestCase
{
    /** @var ObjectManager */
    private $objectManager;

    /** @var GetMaskedQuoteIdByReservedOrderId */
    private $getMaskedQuoteIdByReservedOrderId;

    /** @var SerializerInterface */
    private $jsonSerializer;

    /** @var GraphQlRequest */
    private $graphQlRequest;

    /** @var ZendClient|MockObject|InvocationMocker */
    private $clientMock;

    /** @var CustomerTokenServiceInterface */
    private $customerTokenService;

    /** @var Zend_Http_Response */
    protected $responseMock;

    protected function setUp() : void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->jsonSerializer = $this->objectManager->get(SerializerInterface::class);
        $this->graphQlRequest = $this->objectManager->create(GraphQlRequest::class);
        $this->getMaskedQuoteIdByReservedOrderId = $this->objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
        $this->customerTokenService = $this->objectManager->get(CustomerTokenServiceInterface::class);
        $this->clientMock = $this->createMock(ZendClient::class);
        $this->responseMock = $this->createMock(Zend_Http_Response::class);
        $this->clientMock->method('request')
            ->willReturn($this->responseMock);
        $this->clientMock->method('setUri')
            ->with('https://apitest.authorize.net/xml/v1/request.api');
        $clientFactoryMock = $this->createMock(ZendClientFactory::class);
        $clientFactoryMock->method('create')
            ->willReturn($this->clientMock);
        $this->objectManager->addSharedInstance($clientFactoryMock, ZendClientFactory::class);
    }

    /**
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/active 1
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/environment sandbox
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/login someusername
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/trans_key somepassword
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/trans_signature_key abc
     * @magentoDataFixture Magento/Sales/_files/default_rollback.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/AuthorizenetGraphQl/_files/simple_product_authorizenet.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoDataFixture Magento/AuthorizenetGraphQl/_files/set_new_shipping_address_authorizenet.php
     * @magentoDataFixture Magento/AuthorizenetGraphQl/_files/set_new_billing_address_authorizenet.php
     * @magentoDataFixture Magento/AuthorizenetGraphQl/_files/add_simple_products_authorizenet.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_flatrate_shipping_method.php
     */
    public function testDispatchToPlaceOrderWithRegisteredCustomer(): void
    {
        $paymentMethod = 'authorizenet_acceptjs';
        $cartId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query
            = <<<QUERY
 mutation {
  setPaymentMethodOnCart(input: {
      cart_id: "$cartId"
      payment_method: {
          code: "$paymentMethod"
          authorizenet_acceptjs: 
            {opaque_data_descriptor: "mydescriptor",
             opaque_data_value: "myvalue",
             cc_last_4: 1111}
      }
  }) {    
       cart {
          selected_payment_method {
          code
      }
    }
  }
    placeOrder(input: {cart_id: "$cartId"}) {
      order {
        order_id
      }
    }
}
QUERY;

        $customerToken = $this->customerTokenService->createCustomerAccessToken('customer@example.com', 'password');
        $requestHeaders = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $customerToken
        ];
        // phpcs:ignore Magento2.Security.IncludeFile
        $expectedRequest = include __DIR__ . '/../../../_files/request_authorize_customer.php';
        // phpcs:ignore Magento2.Security.IncludeFile
        $authorizeResponse = include __DIR__ . '/../../../_files/response_authorize.php';

        $this->clientMock->method('setRawData')
            ->with(json_encode($expectedRequest), 'application/json');

        $this->responseMock->method('getBody')->willReturn(json_encode($authorizeResponse));

        $response = $this->graphQlRequest->send($query, [], '', $requestHeaders);
        $responseData = $this->jsonSerializer->unserialize($response->getContent());

        $this->assertArrayNotHasKey('errors', $responseData, 'Response has errors');
        $this->assertTrue(
            isset($responseData['data']['setPaymentMethodOnCart']['cart']['selected_payment_method']['code'])
        );
        $this->assertEquals(
            $paymentMethod,
            $responseData['data']['setPaymentMethodOnCart']['cart']['selected_payment_method']['code']
        );

        $this->assertTrue(
            isset($responseData['data']['placeOrder']['order']['order_id'])
        );

        $this->assertEquals(
            'test_quote',
            $responseData['data']['placeOrder']['order']['order_id']
        );
    }

    protected function tearDown()
    {
        $this->objectManager->removeSharedInstance(ZendClientFactory::class);
    }
}
