<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalGraphQl\Model\Resolver\Guest;

use Magento\Framework\App\Request\Http;
use Magento\Paypal\Model\Api\Nvp;
use Magento\PaypalGraphQl\PaypalPayflowProAbstractTest;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Model\QuoteIdToMaskedQuoteId;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\UrlInterface;
use Magento\Framework\DataObject;

/**
 * Test ExpressSetPaymentMethodTest graphql endpoint for guest
 *
 * @magentoAppArea graphql
 */
class PaypalPayflowProSetPaymentMethodTest extends PaypalPayflowProAbstractTest
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

        $this->objectManager = Bootstrap::getObjectManager();
        $this->graphqlController = $this->objectManager->get(\Magento\GraphQl\Controller\GraphQl::class);
        $this->request = $this->objectManager->create(Http::class);
    }

    /**
     * Test end to end test to process a paypal payflow pro order
     *
     * @return void
     * @magentoDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/guest/set_guest_email.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_new_billing_address.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_flatrate_shipping_method.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testResolveGuest(): void
    {
        $paymentMethod = 'payflowpro';
        $this->enablePaymentMethod($paymentMethod);

        $reservedQuoteId = 'test_quote';
        $payerId = 'PAYER123456';
        $token = 'EC-TOKEN1234';
        $correlationId = 'c123456789';

        $cart = $this->getQuoteByReservedOrderId($reservedQuoteId);
        $cartId = $this->quoteIdToMaskedId->execute((int)$cart->getId());

        $url = $this->objectManager->get(UrlInterface::class);
        $baseUrl = $url->getBaseUrl();

        $query = <<<QUERY
mutation {
    setPaymentMethodOnCart(input: {
        payment_method: {
          code: "{$paymentMethod}",
          additional_data: {
            payflowpro: {
              cc_details: {
                 cc_exp_month: "12",
                 cc_exp_year: "2030",
                 cc_last_4: "1111",
                 cc_type: "IV",
              }
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
      createPayflowProToken(
        input: {
          cart_id:"{$cartId}",
          urls: {
            cancel_url: "{$baseUrl}paypal/transparent/cancel/"
            error_url: "{$baseUrl}paypal/transparent/error/"
            return_url: "{$baseUrl}paypal/transparent/response/"
          }
        }
      ) {
          response_message
          result
          result_code
          secure_token
          secure_token_id
        }
      }
      setPaymentMethodOnCart(input: {
        payment_method: {
          code: "{$paymentMethod}",
          additional_data: {
            payflowpro: {
              paypalPayload: "$token"
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
      placeOrder(input: {cart_id: "{$cartId}"}) {
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
        $headers = $this->objectManager->create(\Zend\Http\Headers::class)
            ->addHeaders(['Content-Type' => 'application/json']);
        $this->request->setHeaders($headers);

        $paypalResponse = new DataObject(
            [
                'result' => '0',
                'securetoken' => 'mysecuretoken',
                'securetokenid' => 'mysecuretokenid',
                'respmsg' => 'Approved',
                'result_code' => '0',
            ]
        );

        $this->gatewayMock
            ->expects($this->at(0))
            ->method('postRequest')
            ->willReturn($paypalResponse);

        $paypalRequestPlaceOrder = include __DIR__ . '/../../../_files/paypal_place_order_request.php';

        $this->gatewayMock
            ->expects($this->at(1))
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

        $response = $this->graphqlController->dispatch($this->request);
        $responseData = $this->json->unserialize($response->getContent());

        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('createPayflowProToken', $responseData['data']);
        $createTokenData = $responseData['data']['createPayflowProToken'];
        $this->assertArrayNotHasKey('errors', $responseData);
        $this->assertEquals($paypalResponse->getData('securetoken'), $createTokenData['secure_token']);
        $this->assertEquals($paypalResponse->getData('securetokenid'), $createTokenData['secure_token_id']);

        $this->assertTrue(
            isset($responseData['data']['setPaymentMethodOnCart']['cart']['selected_payment_method']['code'])
        );
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
}
