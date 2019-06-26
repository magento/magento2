<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalGraphQl\Model\Resolver\Customer;

use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\DataObject;
use Magento\Framework\Math\Random;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\GraphQl\Controller\GraphQl;
use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\Paypal\Model\Payflow\Request;
use Magento\Paypal\Model\Payflow\Service\Gateway;
use Magento\Quote\Model\QuoteIdToMaskedQuoteId;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\UrlInterface;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * End to end place order test using payflow_link via graphql endpoint for registered customer
 *
 * @magentoAppArea graphql
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PlaceOrderWithPayflowLinkTest extends TestCase
{
    /**
     * @var Http
     */
    private $request;

    /**
     * @var SerializerInterface
     */
    private $json;

    /**
     * @var QuoteIdToMaskedQuoteId
     */
    private $quoteIdToMaskedId;

    /** @var  GetMaskedQuoteIdByReservedOrderId */
    private $getMaskedQuoteIdByReservedOrderId;

    /** @var  ObjectManager */
    protected $objectManager;

    /** @var  GraphQl */
    protected $graphqlController;

    /** @var  Gateway|MockObject */
    private $gateway;

    /** @var  Random|MockObject */
    private $mathRandom;

    /** @var  Request|MockObject */
    private $payflowRequest;

    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->request = $this->objectManager->create(Http::class);
        $this->json = $this->objectManager->get(SerializerInterface::class);
        $this->getMaskedQuoteIdByReservedOrderId = $this->objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
        $this->quoteIdToMaskedId = $this->objectManager->get(QuoteIdToMaskedQuoteId::class);

        $this->graphqlController = $this->objectManager->get(GraphQl::class);

        $this->mathRandom = $this->getMockBuilder(Random::class)
            ->getMock();
        $this->gateway = $this->getMockBuilder(Gateway::class)
            ->disableOriginalConstructor()
            ->setMethods(['postRequest'])
            ->getMock();

        $requestFactory = $this->getMockBuilder(\Magento\Paypal\Model\Payflow\RequestFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->payflowRequest = $this->getMockBuilder(\Magento\Paypal\Model\Payflow\Request::class)
            ->disableOriginalConstructor()
            ->getMock();
        $requestFactory->expects($this->any())->method('create')->will($this->returnValue($this->payflowRequest));

        $this->objectManager->addSharedInstance($this->gateway, Gateway::class);
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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testResolvePlaceOrderWithPayflowLinkForCustomer(): void
    {
        $paymentMethod = 'payflow_link';
        $cartId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $url = $this->objectManager->get(UrlInterface::class);
        $baseUrl = $url->getBaseUrl();

        $query
            = <<<QUERY
 mutation {
  setPaymentMethodOnCart(input: {
      cart_id: "$cartId"
      payment_method: {
          code: "$paymentMethod"
          additional_data: {
            payflow_link: 
            {
           cancel_url:"{$baseUrl}paypal/payflow/cancelPayment"
           return_url:"{$baseUrl}paypal/payflow/returnUrl"
          }
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
        order_id
      }
    }
}
QUERY;

        $postData = $this->json->serialize(['query' => $query]);
        $this->request->setPathInfo('/graphql');
        $this->request->setMethod('POST');
        $this->request->setContent($postData);
        
        /** @var \Magento\Integration\Model\Oauth\Token $tokenModel */
        $tokenModel = $this->objectManager->create(\Magento\Integration\Model\Oauth\Token::class);
        $customerToken = $tokenModel->createCustomerToken(1)->getToken();
        /** @var \Magento\Framework\Webapi\Request $webApiRequest */
        $webApiRequest = $this->objectManager->get(\Magento\Framework\Webapi\Request::class);
        $webApiRequest->getHeaders()
            ->addHeaderLine('Content-Type', 'application/json')
            ->addHeaderLine('Accept', 'application/json')
            ->addHeaderLine('Authorization', 'Bearer ' . $customerToken);
        $this->request->setHeaders($webApiRequest->getHeaders());

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

        $this->payflowRequest->expects($this->any())
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
                ['USER2', '4b102efb018ad34bacea669f401fc8cb', $this->returnSelf()]
            );

        $response = $this->graphqlController->dispatch($this->request);
        $responseData = $this->json->unserialize($response->getContent());

        $this->assertArrayNotHasKey('errors', $responseData);
        $this->assertArrayHasKey('data', $responseData);

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

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $this->objectManager->removeSharedInstance(Gateway::class);
    }
}
