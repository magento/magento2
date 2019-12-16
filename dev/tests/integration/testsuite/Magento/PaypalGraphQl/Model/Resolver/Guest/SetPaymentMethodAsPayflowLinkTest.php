<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalGraphQl\Model\Resolver\Guest;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\GraphQl\Service\GraphQlRequest;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Quote\Model\QuoteRepository;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Test SetPayment method for payflow_link and validate the additional information
 *
 * @magentoAppArea graphql
 */
class SetPaymentMethodAsPayflowLinkTest extends TestCase
{
    /** @var GraphQlRequest */
    private $graphQlRequest;

    /** @var SerializerInterface */
    private $json;

    /** @var GetMaskedQuoteIdByReservedOrderId */
    private $getMaskedQuoteIdByReservedOrderId;

    /** @var ObjectManager */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->json = $this->objectManager->get(SerializerInterface::class);
        $this->getMaskedQuoteIdByReservedOrderId = $this->objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
        $this->graphQlRequest = $this->objectManager->create(GraphQlRequest::class);
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
     */
    public function testSetPayflowLinkAsPaymentMethod(): void
    {
        $paymentMethod = 'payflow_link';
        $maskedCartId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query
            = <<<QUERY
 mutation {
  setPaymentMethodOnCart(input: {
      cart_id: "$maskedCartId"
      payment_method: {
          code: "$paymentMethod"
            payflow_link: {           
               return_url:"paypal/payflow/link/success"
               cancel_url:"paypal/payflow/link/cancel"
               error_url:"paypal/payflow/link/error"
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
        $responseData = $this->json->unserialize($response->getContent());

        $this->assertArrayNotHasKey('errors', $responseData);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertEquals(
            $paymentMethod,
            $responseData['data']['setPaymentMethodOnCart']['cart']['selected_payment_method']['code']
        );

        $maskedQuoteIdToQuoteId = $this->objectManager->get(MaskedQuoteIdToQuoteIdInterface::class);
        $quoteId = $maskedQuoteIdToQuoteId->execute($maskedCartId);
        /** @var QuoteRepository $quoteRepository */
        $quoteRepository = $this->objectManager->get(QuoteRepository::class);
        $quote = $quoteRepository->get($quoteId);

        $payment = $quote->getPayment();
        $this->assertEquals(
            "paypal/payflow/link/cancel",
            $payment->getAdditionalInformation('cancel_url')
        );
        $this->assertEquals(
            "paypal/payflow/link/success",
            $payment->getAdditionalInformation('return_url')
        );
        $this->assertEquals(
            "paypal/payflow/link/error",
            $payment->getAdditionalInformation('error_url')
        );
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
     */
    public function testInvalidUrl(): void
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
            payflow_link: {           
               return_url:"http://magento.com/paypal/payflow/link/success"
               cancel_url:"paypal/payflow/link/cancel"
               error_url:"paypal/payflow/link/error"
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
        $responseData = $this->json->unserialize($response->getContent());

        $this->assertArrayHasKey('errors', $responseData);
        $expectedExceptionMessage = "Invalid Url.";
        $actualError = $responseData['errors'][0];
        $this->assertEquals($expectedExceptionMessage, $actualError['message']);
        $this->assertEquals(GraphQlInputException::EXCEPTION_CATEGORY, $actualError['extensions']['category']);
    }
}
