<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalGraphQl\Model\Resolver\Guest;

use Magento\Framework\App\Request\Http;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\GraphQl\Controller\GraphQl;
use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\Paypal\Model\Payflow\Request;
use Magento\Paypal\Model\Payflow\Service\Gateway;
use Magento\Quote\Model\Quote;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\UrlInterface;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * End to end place order test using payflow_link via graphql endpoint for guest
 *
 * @magentoAppArea graphql
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SetPaymentMethodAsPayflowLinkTest extends TestCase
{
    /**
     * @var Http
     */
    private $request;

    /**
     * @var SerializerInterface
     */
    private $json;

    /** @var  GetMaskedQuoteIdByReservedOrderId */
    private $getMaskedQuoteIdByReservedOrderId;

    /** @var  ObjectManager */
    protected $objectManager;

    /** @var  GraphQl */
    protected $graphqlController;

    /** @var  Gateway|MockObject */
    private $gateway;

    /** @var  Request|MockObject */
    private $payflowRequest;

    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->request = $this->objectManager->create(Http::class);
        $this->json = $this->objectManager->get(SerializerInterface::class);
        $this->getMaskedQuoteIdByReservedOrderId = $this->objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
        $this->graphqlController = $this->objectManager->get(GraphQl::class);
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
     * Test SetPayment method for payflow_link and validate that the additional information is set on the quote
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
    public function testSetPayflowLinkAsPaymentMethod(): void
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
           return_url:"{$baseUrl}paypal/payflow/returnUrl"
           cancel_url:"{$baseUrl}paypal/payflow/cancelPayment"
           error_url:"{$baseUrl}paypal/payflow/errorUrl"
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
}
QUERY;

        $postData = $this->json->serialize(['query' => $query]);
        $this->request->setPathInfo('/graphql');
        $this->request->setMethod('POST');
        $this->request->setContent($postData);
        $headers = $this->objectManager->create(\Zend\Http\Headers::class)
            ->addHeaders(['Content-Type' => 'application/json']);
        $this->request->setHeaders($headers);

        $response = $this->graphqlController->dispatch($this->request);
        $responseData = $this->json->unserialize($response->getContent());

        $this->assertArrayNotHasKey('errors', $responseData);
        $this->assertArrayHasKey('data', $responseData);

        $this->assertEquals(
            $paymentMethod,
            $responseData['data']['setPaymentMethodOnCart']['cart']['selected_payment_method']['code']
        );
        /** @var Quote $quote */
        $quote = $this->objectManager->get(Quote::class);

        $quote->load('test_quote', 'reserved_order_id');
        $payment = $quote->getPayment();
        $actualPaymentAdditionalInformation = $payment->getAdditionalInformation('payflow_link');
        $this->assertEquals("{$baseUrl}paypal/payflow/cancelPayment", $payment->getAdditionalInformation('cancel_url'));
        $this->assertEquals("{$baseUrl}paypal/payflow/returnUrl", $payment->getAdditionalInformation('return_url'));
        $this->assertEquals("{$baseUrl}paypal/payflow/errorUrl", $payment->getAdditionalInformation('error_url'));
    }

    /**
     * Test invalid redirect url
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
    public function testInvalidUrl(): void
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
           return_url:"{$baseUrl}paypal/payflow/returnUrl"
           cancel_url:"{$baseUrl}paypal/payflow/cancelPayment"
           error_url:"/not/a/validUrl"
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
}
QUERY;

        $postData = $this->json->serialize(['query' => $query]);
        $this->request->setPathInfo('/graphql');
        $this->request->setMethod('POST');
        $this->request->setContent($postData);
        $headers = $this->objectManager->create(\Zend\Http\Headers::class)
            ->addHeaders(['Content-Type' => 'application/json']);
        $this->request->setHeaders($headers);

        $expectedExceptionMessage = "Invalid URL '/not/a/validUrl'.";

        $response = $this->graphqlController->dispatch($this->request);
        $responseData = $this->json->unserialize($response->getContent());
        $this->assertArrayHasKey('errors', $responseData);
        $actualError = $responseData['errors'][0];
        $this->assertEquals($expectedExceptionMessage, $actualError['message']);
        $this->assertEquals(GraphQlInputException::EXCEPTION_CATEGORY, $actualError['category']);
    }
}
