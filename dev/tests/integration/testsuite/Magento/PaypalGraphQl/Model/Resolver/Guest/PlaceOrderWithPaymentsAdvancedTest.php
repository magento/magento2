<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalGraphQl\Model\Resolver\Guest;

use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\DataObject;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\GraphQl\Service\GraphQlRequest;
use Magento\Paypal\Model\Payflow\Request;
use Magento\Paypal\Model\Payflow\RequestFactory;
use Magento\Paypal\Model\Payflow\Service\Gateway;
use Magento\Paypal\Model\Payflowlink;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * End to end place order test using PayPal payments advanced via GraphQl
 *
 * @magentoAppArea graphql
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PlaceOrderWithPaymentsAdvancedTest extends TestCase
{
    /** @var GraphQlRequest */
    private $graphQlRequest;

    /** @var GetMaskedQuoteIdByReservedOrderId */
    private $getMaskedQuoteIdByReservedOrderId;

    /** @var ObjectManager */
    private $objectManager;

    /** @var Gateway|MockObject */
    private $gateway;

    /** @var Request|MockObject */
    private $paymentRequest;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->graphQlRequest = $this->objectManager->create(GraphQlRequest::class);

        $this->getMaskedQuoteIdByReservedOrderId = $this->objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
        $this->gateway = $this->getMockBuilder(Gateway::class)
            ->disableOriginalConstructor()
            ->setMethods(['postRequest'])
            ->getMock();

        $requestFactory = $this->getMockBuilder(RequestFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentRequest = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->setMethods(['__call','setData'])
            ->getMock();
        $this->paymentRequest->method('__call')
            ->willReturnCallback(
                function ($method) {
                    if (strpos($method, 'set') === 0) {
                        return $this->paymentRequest;
                    }
                    return null;
                }
            );

        $requestFactory->method('create')->willReturn($this->paymentRequest);
        $this->objectManager->addSharedInstance($this->gateway, Gateway::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->objectManager->removeSharedInstance(Gateway::class);
    }

    /**
     * Test successful place Order with Payments Advanced
     *
     * @magentoConfigFixture default_store payment/payflow_advanced/active 1
     * @magentoConfigFixture default_store payment/payflow_advanced/sandbox_flag 1
     * @magentoDataFixture Magento/Sales/_files/default_rollback.php
     * @magentoDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/guest/set_guest_email.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_new_billing_address.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_flatrate_shipping_method.php
     * @return void
     */
    public function testResolvePlaceOrderWithPaymentsAdvanced(): void
    {
        $paymentMethod = 'payflow_advanced';
        $cartId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $productMetadata = ObjectManager::getInstance()->get(ProductMetadataInterface::class);
        $button = 'Magento_Cart_' . $productMetadata->getEdition();

        $payflowLinkResponse = new DataObject(
            [
                'result' => '0',
                'respmsg' => 'Approved',
                'pnref' => 'V19A3D27B61E',
                'result_code' => '0'
            ]
        );
        $this->gateway->expects($this->once())
            ->method('postRequest')
            ->willReturn($payflowLinkResponse);

        $this->paymentRequest
            ->method('setData')
            ->willReturnMap(
                [
                    [
                        'user' => null,
                        'vendor' => null,
                        'partner' => null,
                        'pwd' => null,
                        'verbosity' => null,
                        'BUTTONSOURCE' => $button,
                        'tender' => 'C',
                    ],
                    $this->returnSelf()
                ],
                ['USER1', 1, $this->returnSelf()],
                ['USER2', 'USER2SilentPostHash', $this->returnSelf()]
            );

        $responseData = $this->setPaymentMethodAndPlaceOrder($cartId, $paymentMethod);

        $this->assertArrayNotHasKey('errors', $responseData);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertEquals(
            $paymentMethod,
            $responseData['data']['setPaymentMethodOnCart']['cart']['selected_payment_method']['code']
        );
        $this->assertNotEmpty(isset($responseData['data']['placeOrder']['order']['order_number']));
        $this->assertEquals('test_quote', $responseData['data']['placeOrder']['order']['order_number']);
    }

    /**
     * Test place Order with Payments Advanced with Invalid Url.
     *
     * @magentoConfigFixture default_store payment/payflow_advanced/active 1
     * @magentoConfigFixture default_store payment/payflow_advanced/sandbox_flag 1
     * @magentoDataFixture Magento/Sales/_files/default_rollback.php
     * @magentoDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/guest/set_guest_email.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_new_billing_address.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_flatrate_shipping_method.php
     * @return void
     */
    public function testResolvePaymentsAdvancedWithInvalidUrl(): void
    {
        $paymentMethod = 'payflow_advanced';
        $cartId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $responseData = $this->setPaymentMethodWithInValidUrl($cartId, $paymentMethod);
        $expectedExceptionMessage = "Invalid Url.";
        $this->assertArrayHasKey('errors', $responseData);
        $actualError = $responseData['errors'][0];
        $this->assertEquals($expectedExceptionMessage, $actualError['message']);
        $this->assertEquals(GraphQlInputException::EXCEPTION_CATEGORY, $actualError['extensions']['category']);
    }

    /**
     * Test place Order with PaymentAdvanced with a declined status
     *
     * @magentoConfigFixture default_store payment/payflow_advanced/active 1
     * @magentoConfigFixture default_store payment/payflow_advanced/sandbox_flag 1
     * @magentoDataFixture Magento/Sales/_files/default_rollback.php
     * @magentoDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/guest/set_guest_email.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_new_billing_address.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_flatrate_shipping_method.php
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testResolveWithPaymentAdvancedDeclined(): void
    {
        $paymentMethod = 'payflow_advanced';
        $cartId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $resultCode = Payflowlink::RESPONSE_CODE_DECLINED_BY_FILTER;
        $exception = new \Zend_Http_Client_Exception(__('Declined response message from PayPal gateway'));
        //Exception message is transformed into more controlled message
        $expectedExceptionMessage =
            "Unable to place order: Payment Gateway is unreachable at the moment. Please use another payment option.";

        $this->paymentRequest->method('setData')
            ->with(
                [
                    [
                        'invnum' => 'test_quote',
                        'amt' => '40.00',
                        'pnref' => 'TEST123PNREF',
                        'USER2' => '1EncryptedSilentPostHash',
                        'result' => $resultCode,
                        'trxtype' => 'A',

                    ]
                ]
            )
            ->willReturnSelf();

        $this->gateway->method('postRequest')->willThrowException($exception);

        $responseData = $this->setPaymentMethodAndPlaceOrder($cartId, $paymentMethod);
        $this->assertArrayHasKey('errors', $responseData);
        $actualError = $responseData['errors'][0];
        $this->assertEquals($expectedExceptionMessage, $actualError['message']);
        $this->assertEquals(GraphQlInputException::EXCEPTION_CATEGORY, $actualError['extensions']['category']);
    }

    /**
     * Send setPaymentMethodOnCart and placeOrder mutations and return response content
     *
     * @param string $cartId
     * @param string $paymentMethod
     * @return array
     */
    private function setPaymentMethodAndPlaceOrder(string $cartId, string $paymentMethod): array
    {
        $serializer = $this->objectManager->get(SerializerInterface::class);
        $query
            = <<<QUERY
 mutation {
  setPaymentMethodOnCart(input: {
      cart_id: "$cartId"
      payment_method: {
          code: "$paymentMethod"
          payflow_link: {
             cancel_url:"paypal/payflowadvanced/customcancel"
             return_url:"paypal/payflowadvanced/customreturn"
             error_url:"paypal/payflowadvanced/customerror"
          }
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
      order_number
    }
  }
}
QUERY;

        $response = $this->graphQlRequest->send($query);
        $responseContent = $serializer->unserialize($response->getContent());

        return $responseContent;
    }

    /**
     * Send setPaymentMethodOnCart and placeOrder mutations and return response content
     *
     * @param string $cartId
     * @param string $paymentMethod
     * @return array
     */
    private function setPaymentMethodWithInValidUrl(string $cartId, string $paymentMethod): array
    {
        $serializer = $this->objectManager->get(SerializerInterface::class);
        $query
            = <<<QUERY
 mutation {
  setPaymentMethodOnCart(input: {
      cart_id: "$cartId"
      payment_method: {
          code: "$paymentMethod"
          payflow_link: {
             cancel_url:"paypal/payflowadvanced/cancel"
             return_url:"http://localhost/paypal/payflowadvanced/return"
             error_url:"paypal/payflowadvanced/error"
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

        $response = $this->graphQlRequest->send($query);
        $responseContent = $serializer->unserialize($response->getContent());

        return $responseContent;
    }
}
