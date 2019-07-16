<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalGraphQl\Model\Resolver\Customer;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Webapi\Request;
use Magento\Paypal\Model\Api\Nvp;
use Magento\PaypalGraphQl\PaypalExpressAbstractTest;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Model\QuoteIdToMaskedQuoteId;

/**
 * Test createPaypalExpressToken graphql endpoint for customer
 *
 * @magentoAppArea graphql
 */
class PaypalExpressTokenTest extends PaypalExpressAbstractTest
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
     * Test create paypal token for customer
     *
     * @param string $paymentMethod
     * @dataProvider getPaypalCodesProvider
     * @magentoConfigFixture default_store paypal/wpp/sandbox_flag 1
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
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
        $cartId = $cart->getId();
        $maskedCartId = $this->quoteIdToMaskedId->execute((int) $cartId);

        $query = $this->getCreateTokenMutation($maskedCartId, $paymentMethod);

        $postData = $this->json->serialize(['query' => $query]);
        $this->request->setPathInfo('/graphql');
        $this->request->setMethod('POST');
        $this->request->setContent($postData);

        /** @var \Magento\Integration\Model\Oauth\Token $tokenModel */
        $tokenModel = $this->objectManager->create(\Magento\Integration\Model\Oauth\Token::class);
        $customerToken = $tokenModel->createCustomerToken(1)->getToken();

        $webApiRequest = $this->objectManager->get(Request::class);
        $webApiRequest->getHeaders()
            ->addHeaderLine('Content-Type', 'application/json')
            ->addHeaderLine('Accept', 'application/json')
            ->addHeaderLine('Authorization', 'Bearer ' . $customerToken);
        $this->request->setHeaders($webApiRequest->getHeaders());

        $paypalRequest = include __DIR__ . '/../../../_files/customer_paypal_create_token_request.php';
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

        $response = $this->graphqlController->dispatch($this->request);
        $responseData = $this->json->unserialize($response->getContent());
        $createTokenData = $responseData['data']['createPaypalExpressToken'];
        $this->assertArrayNotHasKey('errors', $responseData);
        $this->assertEquals($paypalResponse['TOKEN'], $createTokenData['token']);
        $this->assertArrayHasKey('paypal_urls', $createTokenData);
    }

    /**
     * Paypal method codes provider
     *
     * @return array
     */
    public function getPaypalCodesProvider(): array
    {
        return [
            ['paypal_express'],
            ['payflow_express'],
        ];
    }
}
