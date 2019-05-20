<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalGraphQl\Model\Resolver;

use Magento\Framework\App\Request\Http;
use Magento\Paypal\Model\Api\Nvp;
use Magento\PaypalGraphQl\AbstractTest;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Model\QuoteIdToMaskedQuoteId;

/**
 * @magentoAppArea graphql
 */
class PaypalExpressTokenTest extends AbstractTest
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

    protected function setUp()
    {
        parent::setUp();

        $this->request = $this->objectManager->create(Http::class);
        $this->json = $this->objectManager->get(SerializerInterface::class);
        $this->quoteIdToMaskedId = $this->objectManager->get(QuoteIdToMaskedQuoteId::class);
    }

    /**
     * @magentoConfigFixture default_store payment/paypal_express/active 1
     * @magentoConfigFixture default_store payment/paypal_express/merchant_id test_merchant_id
     * @magentoConfigFixture default_store payment/paypal_express/wpp/api_username test_username
     * @magentoConfigFixture default_store payment/paypal_express/wpp/api_password test_password
     * @magentoConfigFixture default_store payment/paypal_express/wpp/api_signature test_signature
     * @magentoConfigFixture default_store payment/paypal_express/payment_action Authorization
     * @magentoConfigFixture default_store paypal/wpp/sandbox_flag 1
     * @magentoDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/guest/set_guest_email.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_new_billing_address.php
     */
    public function testResolveGuest()
    {
        $reservedQuoteId = 'test_quote';
        $cart = $this->getQuoteByReservedOrderId($reservedQuoteId);

        $cartId = $this->quoteIdToMaskedId->execute((int)$cart->getId());
        $paymentMethod = "paypal_express";
        $query = <<<QUERY
mutation {
    createPaypalExpressToken(input: {
        cart_id: "{$cartId}",
        code: "{$paymentMethod}",
        express_button: true
    })
    {
        __typename
        token
        paypal_urls{
            start
            edit
        }
        method
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

        $paypalRequest = include __DIR__ . '/../../_files/guest_paypal_create_token_request.php';
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

        $response = $this->graphqlController->dispatch($this->request);
        $responseData = $this->json->unserialize($response->getContent());
        $createTokenData = $responseData['data']['createPaypalExpressToken'];

        $this->assertArrayNotHasKey('errors', $responseData);
        $this->assertEquals($paypalResponse['TOKEN'], $createTokenData['token']);
        $this->assertEquals($paymentMethod, $createTokenData['method']);
        $this->assertArrayHasKey('paypal_urls', $createTokenData);
    }
}
