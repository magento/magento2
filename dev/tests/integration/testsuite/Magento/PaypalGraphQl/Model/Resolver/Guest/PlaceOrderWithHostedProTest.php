<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalGraphQl\Model\Resolver\Guest;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\GraphQl\Service\GraphQlRequest;
use Magento\Paypal\Model\Api\Nvp;
use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\Hostedpro;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Paypal\Model\Api\Type\Factory as ApiFactory;

/**
 * End to end place order test using hostedpro via graphql endpoint
 *
 * @magentoAppArea graphql
 */
class PlaceOrderWithHostedProTest extends TestCase
{
    /** @var string */
    private $paymentMethod = Config::METHOD_HOSTEDPRO;

    /** @var GraphQlRequest */
    private $graphQlRequest;

    /** @var SerializerInterface */
    private $json;

    /** @var GetMaskedQuoteIdByReservedOrderId */
    private $getMaskedQuoteIdByReservedOrderId;

    /** @var ObjectManager */
    private $objectManager;

    /** @var Nvp|MockObject */
    private $nvpMock;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->graphQlRequest = $this->objectManager->create(GraphQlRequest::class);
        $this->json = $this->objectManager->get(SerializerInterface::class);
        $this->getMaskedQuoteIdByReservedOrderId = $this->objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);

        $this->nvpMock = $this->getMockBuilder(Nvp::class)
            ->disableOriginalConstructor()
            ->setMethods(['call'])
            ->getMock();

        $apiFactoryMock = $this->getMockBuilder(ApiFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $apiFactoryMock->method('create')->willReturn($this->nvpMock);

        $this->objectManager->addSharedInstance($apiFactoryMock, ApiFactory::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->objectManager->removeSharedInstance(ApiFactory::class);
    }

    /**
     * Test successful place order with Hosted Pro
     *
     * @magentoConfigFixture default_store payment/hosted_pro/active 1
     * @magentoConfigFixture default_store paypal/wpp/sandbox_flag 1
     * @magentoConfigFixture default_store paypal/general/merchant_country GB
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
    public function testPlaceOrderWithHostedPro(): void
    {
        $cartId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query
            = <<<QUERY
 mutation {
  setPaymentMethodOnCart(input: {
      cart_id: "$cartId"
      payment_method: {
          code: "$this->paymentMethod"
          hosted_pro: {
              cancel_url:"paypal/hostedpro/customcancel"
              return_url:"paypal/hostedpro/customreturn"
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

        $apiRequestData = require __DIR__ . '/../../../_files/hosted_pro_nvp_request.php';
        $apiResponseData = require __DIR__ . '/../../../_files/hosted_pro_nvp_response.php';

        $this->nvpMock
            ->expects($this->once())
            ->method('call')
            ->with(Hostedpro::BM_BUTTON_METHOD, $apiRequestData)
            ->willReturn($apiResponseData);

        $response = $this->graphQlRequest->send($query);
        $responseData = $this->json->unserialize($response->getContent());

        $this->assertArrayNotHasKey('errors', $responseData);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertEquals(
            $this->paymentMethod,
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
     * Test place order with Hosted Pro with a declined status
     *
     * @magentoConfigFixture default_store payment/hosted_pro/active 1
     * @magentoConfigFixture default_store paypal/wpp/sandbox_flag 1
     * @magentoConfigFixture default_store paypal/general/merchant_country GB
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
    public function testOrderWithHostedProDeclined(): void
    {
        $cartId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query
            = <<<QUERY
 mutation {
  setPaymentMethodOnCart(input: {
      cart_id: "$cartId"
      payment_method: {
          code: "$this->paymentMethod"
          hosted_pro: {
            cancel_url:"paypal/hostedpro/customCancel"
            return_url:"paypal/hostedpro/customReturnUrl"
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

        $exceptionMessage = 'Declined response message from PayPal gateway';
        $exception = new LocalizedException(__($exceptionMessage));
        $expectedExceptionMessage = 'Unable to place order: A server error stopped your order from being placed. ' .
            'Please try to place your order again';

        $this->nvpMock->method('call')->willThrowException($exception);

        $response = $this->graphQlRequest->send($query);
        $responseData = $this->json->unserialize($response->getContent());
        $this->assertArrayHasKey('errors', $responseData);
        $actualError = $responseData['errors'][0];
        $this->assertEquals($expectedExceptionMessage, $actualError['message']);
        $this->assertEquals(GraphQlInputException::EXCEPTION_CATEGORY, $actualError['extensions']['category']);
    }

    /**
     * Test setPaymentMethodOnCart with invalid url inputs
     *
     * @magentoConfigFixture default_store payment/hosted_pro/active 1
     * @magentoConfigFixture default_store paypal/wpp/sandbox_flag 1
     * @magentoConfigFixture default_store paypal/general/merchant_country GB
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
    public function testSetPaymentMethodInvalidUrls()
    {
        $cartId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query
            = <<<QUERY
 mutation {
  setPaymentMethodOnCart(input: {
      cart_id: "$cartId"
      payment_method: {
          code: "$this->paymentMethod"
          hosted_pro: {
            cancel_url:"http://mysite.com/paypal/hostedpro/customCancel"
            return_url:"http://mysite.com/paypal/hostedpro/customReturnUrl"
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

        $expectedExceptionMessage = 'Invalid Url.';

        $response = $this->graphQlRequest->send($query);
        $responseData = $this->json->unserialize($response->getContent());
        $this->assertArrayHasKey('errors', $responseData);
        $actualError = $responseData['errors'][0];
        $this->assertEquals($expectedExceptionMessage, $actualError['message']);
        $this->assertEquals(GraphQlInputException::EXCEPTION_CATEGORY, $actualError['extensions']['category']);
    }
}
