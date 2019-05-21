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
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\GraphQl\Schema\SchemaGeneratorInterface;

/**
 * @magentoAppArea graphql
 */
class PaypalExpressSetPaymentMethodTest extends AbstractTest
{
    /**
     * @var Http
     */
    private $request;

    /**
     * @var SerializerInterface
     */
    private $json;

    /** @var QuoteIdToMaskedQuoteId */
    private $quoteIdToMaskedId;

    protected function setUp()
    {
        parent::setUp();

        $this->request = $this->objectManager->create(Http::class);
        $this->json = $this->objectManager->get(SerializerInterface::class);
        $this->quoteIdToMaskedId = $this->objectManager->get(QuoteIdToMaskedQuoteId::class);

        $this->objectManager = Bootstrap::getObjectManager();
        $this->graphqlController = $this->objectManager->get(\Magento\GraphQl\Controller\GraphQl::class);
        $this->request = $this->objectManager->create(Http::class);
    }

    /**
     * @dataProvider getPaypalCodesProvider
     * @magentoConfigFixture default_store paypal/wpp/sandbox_flag 1
     * @magentoDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/guest/set_guest_email.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_new_billing_address.php
     */
    public function testResolveGuest($paymentMethod)
    {
        $reservedQuoteId = 'test_quote';
        $payerId = 'SQFE93XKTSDRJ';
        $token = 'EC-TOKEN1234';
        $correlationId = 'c123456789';

        $config = $this->objectManager->get(ConfigInterface::class);
        $config->saveConfig('payment/' . $paymentMethod .'/active', '1');

        if ($paymentMethod == 'payflow_express') {
            $config = $this->objectManager->get(ConfigInterface::class);
            $config->saveConfig('payment/payflow_link/active', '1');
        }

        $this->objectManager->get(ReinitableConfigInterface::class)->reinit();

        $cart = $this->getQuoteByReservedOrderId($reservedQuoteId);

        $cartId = $this->quoteIdToMaskedId->execute((int)$cart->getId());

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
    setPaymentMethodOnCart(input: {
        payment_method: {
          code: "{$paymentMethod}",
          additional_data: {
            paypal_express: {
              payer_id: "$payerId",
              token: "$token"
            }
            payflow_express: {
              payer_id: "$payerId",
              token: "$token"
            }
          }
        },
        cart_id: "{$cartId}"})
      {
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

        $paypalRequest = include __DIR__ . '/../../_files/guest_paypal_create_token_request.php';
        $paypalResponse = [
            'TOKEN' => $token,
            'CORRELATIONID' => $correlationId,
            'ACK' => 'Success'
        ];

        if ($paymentMethod == 'payflow_express') {
            $paypalRequest['SOLUTIONTYPE'] = null;
        }

        $this->nvpMock
            ->expects($this->at(0))
            ->method('call')
            ->with(Nvp::SET_EXPRESS_CHECKOUT, $paypalRequest)
            ->willReturn($paypalResponse);

        $paypalRequestDetails = [
            'TOKEN' => $token,
        ];

        $paypalRequestDetailsResponse = include __DIR__ . '/../../_files/guest_paypal_set_payer_id.php';

        $this->nvpMock
            ->expects($this->at(1))
            ->method('call')
            ->with(Nvp::GET_EXPRESS_CHECKOUT_DETAILS, $paypalRequestDetails)
            ->willReturn($paypalRequestDetailsResponse);

        $response = $this->graphqlController->dispatch($this->request);
        $responseData = $this->json->unserialize($response->getContent());
        $createTokenData = $responseData['data']['createPaypalExpressToken'];

        $this->assertArrayNotHasKey('errors', $responseData);
        $this->assertEquals($paypalResponse['TOKEN'], $createTokenData['token']);
        $this->assertEquals($paymentMethod, $createTokenData['method']);
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
