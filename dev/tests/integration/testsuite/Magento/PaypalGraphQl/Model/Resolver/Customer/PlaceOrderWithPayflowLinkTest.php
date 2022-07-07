<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalGraphQl\Model\Resolver\Customer;

use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Math\Random;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\GraphQl\Service\GraphQlRequest;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Integration\Model\Oauth\Token;
use Magento\Paypal\Model\Payflow\Request;
use Magento\Paypal\Model\Payflow\Service\Gateway;
use Magento\Quote\Model\QuoteIdToMaskedQuoteId;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Paypal\Model\Payflow\RequestFactory;

/**
 * End to end place order test using payflow_link via graphql endpoint for registered customer
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

    /** @var QuoteIdToMaskedQuoteId */
    private $quoteIdToMaskedId;

    /** @var GetMaskedQuoteIdByReservedOrderId */
    private $getMaskedQuoteIdByReservedOrderId;

    /** @var ObjectManager */
    private $objectManager;

    /** @var Gateway|MockObject */
    private $gateway;

    /** @var Random|MockObject */
    private $mathRandom;

    /** @var Request|MockObject */
    private $payflowRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->json = $this->objectManager->get(SerializerInterface::class);
        $this->getMaskedQuoteIdByReservedOrderId = $this->objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
        $this->quoteIdToMaskedId = $this->objectManager->get(QuoteIdToMaskedQuoteId::class);
        $this->graphQlRequest = $this->objectManager->create(GraphQlRequest::class);

        $this->mathRandom = $this->getMockBuilder(Random::class)
            ->getMock();
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
            ->getMock();
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
     * Test place order with payflow link
     *
     * @magentoConfigFixture default_store payment/payflow_link/active 1
     * @magentoConfigFixture default_store payment/payflow_link/sandbox_flag 1
     * @magentoDataFixture Magento/Sales/_files/default_rollback.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_new_billing_address.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_flatrate_shipping_method.php
     * @return void
     */
    public function testResolvePlaceOrderWithPayflowLinkForCustomer(): void
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
           error_url:"paypal/payflow/errorUrl"
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

        $this->payflowRequest->method('setData')
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

        /** @var CustomerTokenServiceInterface $tokenService */
        $tokenService = $this->objectManager->get(CustomerTokenServiceInterface::class);
        $customerToken = $tokenService->createCustomerAccessToken('customer@example.com', 'password');

        $requestHeaders = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $customerToken
        ];
        $response = $this->graphQlRequest->send($query, [], '', $requestHeaders);
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
}
