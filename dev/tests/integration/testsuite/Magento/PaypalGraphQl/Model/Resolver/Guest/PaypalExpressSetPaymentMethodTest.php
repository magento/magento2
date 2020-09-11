<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalGraphQl\Model\Resolver\Guest;

use Magento\Paypal\Model\Api\Nvp;
use Magento\PaypalGraphQl\PaypalExpressAbstractTest;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Model\QuoteIdToMaskedQuoteId;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * Test ExpressSetPaymentMethodTest graphql endpoint for guest
 *
 * @magentoAppArea graphql
 */
class PaypalExpressSetPaymentMethodTest extends PaypalExpressAbstractTest
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
     * Test end to end test to process a paypal express order
     *
     * @param string $paymentMethod
     * @return void
     * @dataProvider getPaypalCodesProvider
     * @magentoDataFixture Magento/Sales/_files/default_rollback.php
     * @magentoDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/guest/set_guest_email.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_new_billing_address.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_flatrate_shipping_method.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testResolveGuest(string $paymentMethod): void
    {
        $this->enablePaymentMethod($paymentMethod);
        if ($paymentMethod === 'payflow_express') {
            $this->enablePaymentMethod('payflow_link');
        }

        $reservedQuoteId = 'test_quote';
        $payerId = 'PAYER123456';
        $token = 'EC-TOKEN1234';
        $correlationId = 'c123456789';

        $cart = $this->getQuoteByReservedOrderId($reservedQuoteId);
        $cartId = $this->quoteIdToMaskedId->execute((int)$cart->getId());

        $query = <<<QUERY
mutation {
    createPaypalExpressToken(input: {
        cart_id: "{$cartId}",
        code: "{$paymentMethod}",
        urls: {
            return_url: "paypal/express/return/",
            cancel_url: "paypal/express/cancel/"
            success_url: "checkout/onepage/success/",
            pending_url: "checkout/onepage/pending/"
        }
        express_button: false
    })
    {
        __typename
        token
        paypal_urls{
            start
            edit
        }
    }
    setPaymentMethodOnCart(input: {
        payment_method: {
          code: "{$paymentMethod}",
            paypal_express: {
              payer_id: "$payerId",
              token: "$token"
            }
            payflow_express: {
              payer_id: "$payerId",
              token: "$token"
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
      placeOrder(input: {cart_id: "{$cartId}"}) {
        order {
          order_number
        }
      }
}
QUERY;

        $paypalRequest = include __DIR__ . '/../../../_files/guest_paypal_create_token_request.php';
        $paypalResponse = [
            'TOKEN' => $token,
            'CORRELATIONID' => $correlationId,
            'ACK' => 'Success'
        ];

        if ($paymentMethod == 'payflow_express') {
            $paypalRequest['SOLUTIONTYPE'] = null;
        }

        $paypalRequest['AMT'] = '30.00';
        $paypalRequest['SHIPPINGAMT'] = '10.00';

        $this->nvpMock
            ->expects($this->at(0))
            ->method('call')
            ->with(Nvp::SET_EXPRESS_CHECKOUT, $paypalRequest)
            ->willReturn($paypalResponse);

        $paypalRequestDetails = [
            'TOKEN' => $token,
        ];

        $paypalRequestDetailsResponse = include __DIR__ . '/../../../_files/paypal_set_payer_id_repsonse.php';

        $this->nvpMock
            ->expects($this->at(1))
            ->method('call')
            ->with(Nvp::GET_EXPRESS_CHECKOUT_DETAILS, $paypalRequestDetails)
            ->willReturn($paypalRequestDetailsResponse);

        $paypalRequestPlaceOrder = include __DIR__ . '/../../../_files/paypal_place_order_request.php';

        $this->nvpMock
            ->expects($this->at(2))
            ->method('call')
            ->with(Nvp::DO_EXPRESS_CHECKOUT_PAYMENT, $paypalRequestPlaceOrder)
            ->willReturn(
                [
                    'RESULT' => '0',
                    'PNREF' => 'B7PPAC033FF2',
                    'RESPMSG' => 'Approved',
                    'AVSADDR' => 'Y',
                    'AVSZIP' => 'Y',
                    'TOKEN' => $token,
                    'PAYERID' => $payerId,
                    'PPREF' => '7RK43642T8939154L',
                    'CORRELATIONID' => $correlationId,
                    'PAYMENTTYPE' => 'instant',
                    'PENDINGREASON' => 'authorization',
                ]
            );

        $response = $this->graphQlRequest->send($query);
        $responseData = $this->json->unserialize($response->getContent());

        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('createPaypalExpressToken', $responseData['data']);
        $createTokenData = $responseData['data']['createPaypalExpressToken'];
        $this->assertArrayNotHasKey('errors', $responseData);
        $this->assertEquals($paypalResponse['TOKEN'], $createTokenData['token']);
        $this->assertArrayHasKey('paypal_urls', $createTokenData);

        $this->assertTrue(
            isset($responseData['data']['setPaymentMethodOnCart']['cart']['selected_payment_method']['code'])
        );
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
