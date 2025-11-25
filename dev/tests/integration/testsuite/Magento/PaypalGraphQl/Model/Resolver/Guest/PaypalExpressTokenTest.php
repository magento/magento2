<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalGraphQl\Model\Resolver\Guest;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Paypal\Model\Api\Nvp;
use Magento\PaypalGraphQl\PaypalExpressAbstractTest;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Model\QuoteIdToMaskedQuoteId;

/**
 * Test create PaypalExpressToken graphql endpoint for guest
 *
 * @magentoAppArea graphql
 */
class PaypalExpressTokenTest extends PaypalExpressAbstractTest
{
    /**
     * @var SerializerInterface
     */
    private $json;

    /**
     * @var QuoteIdToMaskedQuoteId
     */
    private $quoteIdToMaskedId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->json = $this->objectManager->get(SerializerInterface::class);
        $this->quoteIdToMaskedId = $this->objectManager->get(QuoteIdToMaskedQuoteId::class);
    }

    /**
     * Test create paypal token for guest
     *
     * @param string $paymentMethod
     * @return void
     * @dataProvider getPaypalCodesProvider
     * @magentoDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/guest/set_guest_email.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_new_billing_address.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_flatrate_shipping_method.php
     */
    public function testResolve($paymentMethod): void
    {
        $this->enablePaymentMethod($paymentMethod);
        if ($paymentMethod === 'payflow_express') {
            $this->enablePaymentMethod('payflow_link');
        }
        $reservedQuoteId = 'test_quote';
        $cart = $this->getQuoteByReservedOrderId($reservedQuoteId);

        $cartId = $this->quoteIdToMaskedId->execute((int)$cart->getId());
        $query = $this->getCreateTokenMutation($cartId, $paymentMethod);

        $paypalRequest = include __DIR__ . '/../../../_files/guest_paypal_create_token_request.php';
        if ($paymentMethod == 'payflow_express') {
            $paypalRequest['SOLUTIONTYPE'] = null;
        }
        $paypalResponse = [
            'TOKEN' => 'EC-TOKEN1234',
            'CORRELATIONID' => 'c123456789',
            'ACK' => 'Success'
        ];

        $this->nvpMock
            ->expects($this->once())
            ->method('call')
            ->with(Nvp::SET_EXPRESS_CHECKOUT, $paypalRequest)
            ->willReturn($paypalResponse);

        $response = $this->graphQlRequest->send($query);
        $responseData = $this->json->unserialize($response->getContent());
        $createTokenData = $responseData['data']['createPaypalExpressToken'];

        $this->assertArrayNotHasKey('errors', $responseData);
        $this->assertEquals($paypalResponse['TOKEN'], $createTokenData['token']);
        $this->assertArrayHasKey('paypal_urls', $createTokenData);
    }

    /**
     * Test create paypal token for guest
     *
     * @param string $paymentMethod
     * @return void
     * @dataProvider getPaypalCodesProvider
     * @magentoDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/guest/set_guest_email.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_new_billing_address.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_flatrate_shipping_method.php
     */
    public function testResolveWithPaypalError($paymentMethod): void
    {
        $this->enablePaymentMethod($paymentMethod);
        if ($paymentMethod === 'payflow_express') {
            $this->enablePaymentMethod('payflow_link');
        }
        $reservedQuoteId = 'test_quote';
        $cart = $this->getQuoteByReservedOrderId($reservedQuoteId);

        $cartId = $this->quoteIdToMaskedId->execute((int)$cart->getId());
        $query = $this->getCreateTokenMutation($cartId, $paymentMethod);

        $paypalRequest = include __DIR__ . '/../../../_files/guest_paypal_create_token_request.php';
        if ($paymentMethod == 'payflow_express') {
            $paypalRequest['SOLUTIONTYPE'] = null;
        }
        $expectedExceptionMessage = "PayPal gateway has rejected request. Sample PayPal Error.";
        $expectedException = new LocalizedException(__($expectedExceptionMessage));

        $this->nvpMock
            ->expects($this->once())
            ->method('call')
            ->with(Nvp::SET_EXPRESS_CHECKOUT, $paypalRequest)
            ->willThrowException($expectedException);

        $response = $this->graphQlRequest->send($query);
        $responseData = $this->json->unserialize($response->getContent());
        $this->assertArrayHasKey('createPaypalExpressToken', $responseData['data']);
        $this->assertEmpty($responseData['data']['createPaypalExpressToken']);
        $this->assertArrayHasKey('errors', $responseData);
        $actualError = $responseData['errors'][0];
        $this->assertEquals($expectedExceptionMessage, $actualError['message']);
        $this->assertEquals(GraphQlInputException::EXCEPTION_CATEGORY, $actualError['extensions']['category']);
    }

    /**
     * Test create paypal token for Invalid Url for guest
     *
     * @param string $paymentMethod
     * @return void
     * @dataProvider getPaypalCodesProvider
     * @magentoDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/guest/set_guest_email.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_new_billing_address.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_flatrate_shipping_method.php
     */
    public function testResolveWithInvalidRedirectUrl($paymentMethod): void
    {
        $this->enablePaymentMethod($paymentMethod);
        if ($paymentMethod === 'payflow_express') {
            $this->enablePaymentMethod('payflow_link');
        }
        $reservedQuoteId = 'test_quote';
        $cart = $this->getQuoteByReservedOrderId($reservedQuoteId);

        $cartId = $this->quoteIdToMaskedId->execute((int)$cart->getId());

        $query = $this->getCreateTokenMutationWithInvalidUrl($cartId, $paymentMethod);
        $expectedExceptionMessage = "Invalid Url.";
        $response = $this->graphQlRequest->send($query);
        $responseData = $this->json->unserialize($response->getContent());
        $this->assertArrayHasKey('createPaypalExpressToken', $responseData['data']);
        $this->assertEmpty($responseData['data']['createPaypalExpressToken']);
        $this->assertArrayHasKey('errors', $responseData);
        $actualError = $responseData['errors'][0];
        $this->assertEquals($expectedExceptionMessage, $actualError['message']);
        $this->assertEquals(GraphQlInputException::EXCEPTION_CATEGORY, $actualError['extensions']['category']);
    }

    /**
     * Paypal method codes provider
     *
     * @return array
     */
    public static function getPaypalCodesProvider(): array
    {
        return [
            ['paypal_express'],
            ['payflow_express'],
        ];
    }

    /**
     * Get GraphQl query for creating Paypal token
     *
     * @param string $cartId
     * @param string $paymentMethod
     * @return string
     */
    protected function getCreateTokenMutationWithInvalidUrl(string $cartId, string $paymentMethod): string
    {
        return <<<QUERY
mutation {
    createPaypalExpressToken(input: {
        cart_id: "{$cartId}",
        code: "{$paymentMethod}",
        urls: {
            return_url: "paypal/express/return/",
            cancel_url: "paypal/express/cancel/"
            success_url: "http://mage.com/checkout/onepage/success/",
            pending_url: "checkout/onepage/pending/"
        }
    })
    {
        __typename
        token
        paypal_urls{
            start
            edit
        }
    }
}
QUERY;
    }
}
