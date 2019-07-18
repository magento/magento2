<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AuthorizenetGraphQl\Model\Resolver\Guest;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\GraphQl\Controller\GraphQl;
use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Quote\Model\Quote\PaymentFactory;
use PHPUnit\Framework\TestCase;
use Zend_Http_Response;

/**
 * Tests end to end Place Order process for non logged in customer using authorizeNet payment
 *
 * @magentoAppArea graphql
 * @magentoDbIsolation disabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PlaceOrderWithAuthorizeNetTest extends TestCase
{
    const CONTENT_TYPE = 'application/json';

    /** @var  ObjectManager */
    private $objectManager;

    /** @var  GetMaskedQuoteIdByReservedOrderId */
    private $getMaskedQuoteIdByReservedOrderId;

    /** @var GraphQl */
    private $graphql;

    /** @var SerializerInterface */
    private $jsonSerializer;

    /** @var Http */
    private $request;

    /** @var ZendClient|MockObject|InvocationMocker */
    private $clientMock;

    /** @var Zend_Http_Response */
    protected $responseMock;

    /** @var  PaymentFactory */
    private $paymentFactory;

    protected function setUp() : void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->graphql = $this->objectManager->get(\Magento\GraphQl\Controller\GraphQl::class);
        $this->jsonSerializer = $this->objectManager->get(SerializerInterface::class);
        $this->request = $this->objectManager->get(Http::class);
        $this->getMaskedQuoteIdByReservedOrderId = $this->objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
        $this->clientMock = $this->createMock(ZendClient::class);
        $this->responseMock = $this->createMock(Zend_Http_Response::class);
        $this->clientMock->method('request')
            ->willReturn($this->responseMock);
        $this->clientMock->method('setUri')
            ->with('https://apitest.authorize.net/xml/v1/request.api');
        $clientFactoryMock = $this->createMock(ZendClientFactory::class);
        $clientFactoryMock->method('create')
            ->willReturn($this->clientMock);
        /** @var PaymentDataObjectFactory $paymentFactory */
        $this->paymentFactory = $this->objectManager->get(PaymentDataObjectFactory::class);
        $this->objectManager->addSharedInstance($clientFactoryMock, ZendClientFactory::class);
    }

    /**
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/active 1
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/environment sandbox
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/login someusername
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/trans_key somepassword
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/trans_signature_key abc
     * @magentoDataFixture Magento/Sales/_files/default_rollback.php
     * @magentoDataFixture Magento/AuthorizenetGraphQl/_files/simple_product_authorizenet.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/guest/set_guest_email.php
     * @magentoDataFixture Magento/AuthorizenetGraphQl/_files/set_new_shipping_address_authorizenet.php
     * @magentoDataFixture Magento/AuthorizenetGraphQl/_files/set_new_billing_address_authorizenet.php
     * @magentoDataFixture Magento/AuthorizenetGraphQl/_files/add_simple_products_authorizenet.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_flatrate_shipping_method.php
     */
    public function testDispatchToPlaceAnOrderWithAuthorizenet(): void
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
          additional_data:
         {authorizenet_acceptjs: 
            {opaque_data_descriptor: "mydescriptor",
             opaque_data_value: "myvalue",
             cc_last_4: 1111}}
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
        $postData = [
            'query' => $query,
            'variables' => null,
            'operationName' => null
        ];
        $this->request->setPathInfo('/graphql');
        $this->request->setMethod('POST');
        $this->request->setContent(json_encode($postData));
        $headers = $this->objectManager->create(\Zend\Http\Headers::class)
            ->addHeaders(['Content-Type' => 'application/json']);
        $this->request->setHeaders($headers);
        // phpcs:ignore Magento2.Security.IncludeFile
        $expectedRequest = include __DIR__ . '/../../../_files/request_authorize.php';
        // phpcs:ignore Magento2.Security.IncludeFile
        $authorizeResponse = include __DIR__ . '/../../../_files/response_authorize.php';

        $this->clientMock->method('setRawData')
            ->with(json_encode($expectedRequest), 'application/json');

        $this->responseMock->method('getBody')->willReturn(json_encode($authorizeResponse));

        $response = $this->graphql->dispatch($this->request);
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
        parent::tearDown();
    }
}
