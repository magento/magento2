<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalGraphQl\Model\Resolver\Guest;

use Laminas\Http\Exception\RuntimeException;
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
 * End to end place order test using payflow_link via graphql endpoint for guest
 *
 * @magentoAppArea graphql
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PlaceOrderWithPayflowLinkTest extends TestCase
{
    /** @var GraphQlRequest */
    private $graphQlRequest;

    /** @var SerializerInterface */
    private $json;

    /** @var GetMaskedQuoteIdByReservedOrderId */
    private $getMaskedQuoteIdByReservedOrderId;

    /** @var ObjectManager */
    private $objectManager;

    /** @var Gateway|MockObject */
    private $gateway;

    /** @var Request|MockObject */
    private $payflowRequest;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->graphQlRequest = $this->objectManager->create(GraphQlRequest::class);
        $this->json = $this->objectManager->get(SerializerInterface::class);
        $this->getMaskedQuoteIdByReservedOrderId = $this->objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
        $this->gateway = $this->getMockBuilder(Gateway::class)
            ->disableOriginalConstructor()
            ->setMethods(['postRequest'])
            ->getMock();

        $requestFactory = $this->getMockBuilder(RequestFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->payflowRequest = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->setMethods(['__call','setData'])
            ->getMock();
        $this->payflowRequest->method('__call')
            ->willReturnCallback(
                function ($method) {
                    if (strpos($method, 'set') === 0) {
                        return $this->payflowRequest;
                    }
                    return null;
                }
            );

        $requestFactory->expects($this->any())->method('create')->willReturn($this->payflowRequest);
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
     * Test successful place Order with Payflow link
     *
     * @magentoConfigFixture default_store payment/payflow_link/active 1
     * @magentoConfigFixture default_store payment/payflow_link/sandbox_flag 1
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
    public function testResolvePlaceOrderWithPayflowLink(): void
    {
        $paymentMethod = 'payflow_link';
        $cartId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query
            = <<<QUERY
 mutation {
  setPaymentMethodOnCart(input: {
      cart_id: "$cartId"
      payment_method: {
          code: "$paymentMethod"
            payflow_link:
            {
           cancel_url:"paypal/payflow/cancel"
           return_url:"paypal/payflow/return"
           error_url:"paypal/payflow/error"
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
      errors {
        message
        code
      }
    }
}
QUERY;

        $productMetadata = ObjectManager::getInstance()->get(ProductMetadataInterface::class);
        $button = 'Magento_2_' . $productMetadata->getEdition();

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

        $this->payflowRequest
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

        $response = $this->graphQlRequest->send($query);
        $responseData = $this->json->unserialize($response->getContent());

        $this->assertArrayNotHasKey('errors', $responseData);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertEquals(
            $paymentMethod,
            $responseData['data']['setPaymentMethodOnCart']['cart']['selected_payment_method']['code']
        );
        $this->assertTrue(
            isset($responseData['data']['placeOrder']['order']['order_number'])
        );
        $this->assertEquals(
            'test_quote',
            $responseData['data']['placeOrder']['order']['order_number']
        );
    }

    /**
     * Test place Order with Payflow link with a declined status
     *
     * @magentoConfigFixture default_store payment/payflow_link/active 1
     * @magentoConfigFixture default_store payment/payflow_link/sandbox_flag 1
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
    public function testResolveWithPayflowLinkDeclined(): void
    {
        $paymentMethod = 'payflow_link';
        $cartId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query
            = <<<QUERY
 mutation {
  setPaymentMethodOnCart(input: {
      cart_id: "$cartId"
      payment_method: {
          code: "$paymentMethod"
            payflow_link:
            {
           cancel_url:"paypal/payflow/cancelPayment"
           return_url:"paypal/payflow/returnUrl"
           error_url:"paypal/payflow/returnUrl"
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
      errors {
        message
        code
      }
    }
}
QUERY;

        $resultCode = Payflowlink::RESPONSE_CODE_DECLINED_BY_FILTER;
        $exception = new RuntimeException(__('Declined response message from PayPal gateway')->render());
        //Exception message is transformed into more controlled message
        $expectedErrorCode = 'UNDEFINED';

        $this->payflowRequest->method('setData')
            ->with(
                [
                    [
                        'invnum' => 'test_quote',
                        'amt' => '30.00',
                        'pnref' => 'TEST123PNREF',
                        'USER2' => '1EncryptedSilentPostHash',
                        'result' => $resultCode,
                        'trxtype' => 'A',

                    ]
                ]
            )
            ->willReturnSelf();

        $this->gateway->method('postRequest')->willThrowException($exception);

        $response = $this->graphQlRequest->send($query);
        $responseData = $this->json->unserialize($response->getContent());
        $this->assertArrayHasKey('errors', $responseData['data']['placeOrder']);
        $actualError = $responseData['data']['placeOrder']['errors'][0];
        $this->assertEquals(
            $expectedErrorCode,
            $actualError['code']
        );
    }
}
