<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalGraphQl\Model\Resolver\Customer;

use Magento\PaypalGraphQl\PaypalPayflowProAbstractTest;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Model\QuoteIdToMaskedQuoteId;
use Magento\Framework\UrlInterface;
use Magento\Framework\DataObject;

/**
 * End to end place order test using payflowpro via graphql endpoint for customer
 *
 * @magentoAppArea graphql
 */
class PlaceOrderWithPayflowProTest extends PaypalPayflowProAbstractTest
{
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

        $this->json = $this->objectManager->get(SerializerInterface::class);
        $this->quoteIdToMaskedId = $this->objectManager->get(QuoteIdToMaskedQuoteId::class);
    }

    /**
     * Test end to end test to process a paypal payflow pro order
     *
     * @return void
     * @magentoDataFixture Magento/Sales/_files/default_rollback.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_new_billing_address.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_flatrate_shipping_method.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testResolveCustomer(): void
    {
        $paymentMethod = 'payflowpro';
        $this->enablePaymentMethod($paymentMethod);
        $reservedQuoteId = 'test_quote';

        $payload = 'BILLTOCITY=CityM&AMT=0.00&BILLTOSTREET=Green+str,+67&VISACARDLEVEL=12&SHIPTOCITY=CityM'
            . '&NAMETOSHIP=John+Smith&ZIP=75477&BILLTOLASTNAME=Smith&BILLTOFIRSTNAME=John'
            . '&RESPMSG=Verified&PROCCVV2=M&STATETOSHIP=AL&NAME=John+Smith&BILLTOZIP=75477&CVV2MATCH=Y'
            . '&PNREF=B70CCC236815&ZIPTOSHIP=75477&SHIPTOCOUNTRY=US&SHIPTOSTREET=Green+str,+67&CITY=CityM'
            . '&HOSTCODE=A&LASTNAME=Smith&STATE=AL&SECURETOKEN=MYSECURETOKEN&CITYTOSHIP=CityM&COUNTRYTOSHIP=US'
            . '&AVSDATA=YNY&ACCT=1111&AUTHCODE=111PNI&FIRSTNAME=John&RESULT=0&IAVS=N&POSTFPSMSG=No+Rules+Triggered&'
            . 'BILLTOSTATE=AL&BILLTOCOUNTRY=US&EXPDATE=0222&CARDTYPE=0&PREFPSMSG=No+Rules+Triggered&SHIPTOZIP=75477&'
            . 'PROCAVS=A&COUNTRY=US&AVSZIP=N&ADDRESS=Green+str,+67&BILLTONAME=John+Smith&'
            . 'ADDRESSTOSHIP=Green+str,+67&'
            . 'AVSADDR=Y&SECURETOKENID=MYSECURETOKENID&SHIPTOSTATE=AL&TRANSTIME=2019-06-24+07%3A53%3A10';

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
                 cc_exp_month: 12,
                 cc_exp_year: 2030,
                 cc_last_4: 1111,
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
      handlePayflowProResponse(input: {
          paypal_payload: "$payload",
          cart_id: "{$cartId}"
        })
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

        /** @var \Magento\Integration\Model\Oauth\Token $tokenModel */
        $tokenModel = $this->objectManager->create(\Magento\Integration\Model\Oauth\Token::class);
        $customerToken = $tokenModel->createCustomerToken(1)->getToken();

        $requestHeaders = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $customerToken
        ];
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

        $this->gatewayMock
            ->expects($this->at(1))
            ->method('postRequest')
            ->willReturn(
                new DataObject(
                    [
                        'result' => '0',
                        'pnref' => 'A70AAC2378BA',
                        'respmsg' => 'Approved',
                        'authcode' => '647PNI',
                        'avsaddr' => 'Y',
                        'avszip' => 'N',
                        'hostcode' => 'A',
                        'procavs' => 'A',
                        'visacardlevel' => '12',
                        'transtime' => '2019-06-24 10:12:03',
                        'firstname' => 'Cristian',
                        'lastname' => 'Partica',
                        'amt' => '14.99',
                        'acct' => '1111',
                        'expdate' => '0221',
                        'cardtype' => '0',
                        'iavs' => 'N',
                        'result_code' => '0',
                    ]
                )
            );

        $response = $this->graphQlRequest->send($query, [], '', $requestHeaders);
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
            isset($responseData['data']['handlePayflowProResponse']['cart']['selected_payment_method']['code'])
        );
        $this->assertEquals(
            $paymentMethod,
            $responseData['data']['handlePayflowProResponse']['cart']['selected_payment_method']['code']
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
