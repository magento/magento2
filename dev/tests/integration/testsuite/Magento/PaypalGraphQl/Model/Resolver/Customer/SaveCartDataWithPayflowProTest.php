<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalGraphQl\Model\Resolver\Customer;

use Magento\Integration\Model\Oauth\Token;
use Magento\PaypalGraphQl\PaypalPayflowProAbstractTest;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Model\QuoteIdToMaskedQuoteId;
use Magento\Framework\DataObject;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\PaymentTokenManagement;
use Magento\Vault\Model\PaymentTokenRepository;

/**
 * End to end place order test using payflowpro via graphql endpoint for customer
 *
 * @magentoAppArea graphql
 */
class SaveCartDataWithPayflowProTest extends PaypalPayflowProAbstractTest
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
     * Place order use payflowpro method and save cart data to future
     *
     * @magentoDataFixture Magento/Sales/_files/default_rollback.php
     * @magentoDataFixture Magento/Sales/_files/default_rollback.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_new_billing_address.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_flatrate_shipping_method.php
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testPlaceOrderAndSaveDataForFuturePayflowPro(): void
    {
        $responseData = $this->placeOrderPayflowPro('is_active_payment_token_enabler: true');
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('createPayflowProToken', $responseData['data']);
        $this->assertNotEmpty($this->getVaultCartData()->getPublicHash());
        $this->assertNotEmpty($this->getVaultCartData()->getTokenDetails());
        $this->assertNotEmpty($this->getVaultCartData()->getGatewayToken());
        $this->assertTrue($this->getVaultCartData()->getIsActive());
        $this->assertTrue($this->getVaultCartData()->getIsVisible());
    }

    /**
     * Place order use payflowpro method and not save cart data to future
     *
     * @magentoDataFixture Magento/Sales/_files/default_rollback.php
     * @magentoDataFixture Magento/Sales/_files/default_rollback.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_new_billing_address.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_flatrate_shipping_method.php
     *
     * @return void
     */
    public function testPlaceOrderAndNotSaveDataForFuturePayflowPro(): void
    {
        $responseData = $this->placeOrderPayflowPro('is_active_payment_token_enabler: false');
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('createPayflowProToken', $responseData['data']);
        $this->assertNotEmpty($this->getVaultCartData()->getPublicHash());
        $this->assertNotEmpty($this->getVaultCartData()->getTokenDetails());
        $this->assertNotEmpty($this->getVaultCartData()->getGatewayToken());
        $this->assertTrue($this->getVaultCartData()->getIsActive());
        $this->assertFalse($this->getVaultCartData()->getIsVisible());
    }

    /**
     * @param $isActivePaymentTokenEnabler
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function placeOrderPayflowPro($isActivePaymentTokenEnabler)
    {
        $paymentMethod = 'payflowpro';
        $this->enablePaymentMethod($paymentMethod);
        $this->enablePaymentMethod('payflowpro_cc_vault');
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

        $query = <<<QUERY
mutation {
    setPaymentMethodOnCart(input: {
        payment_method: {
          code: "{$paymentMethod}",
            payflowpro: {
              {$isActivePaymentTokenEnabler}
              cc_details: {
                 cc_exp_month: 12,
                 cc_exp_year: 2030,
                 cc_last_4: 1111,
                 cc_type: "IV",
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
            cancel_url: "paypal/transparent/cancel/"
            error_url: "paypal/transparent/error/"
            return_url: "paypal/transparent/response/"
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
          order_number
        }
      }
}
QUERY;

        /** @var Token $tokenModel */
        $tokenModel = $this->objectManager->create(Token::class);
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
            ->method('postRequest')
            ->willReturn($paypalResponse);

        $this->gatewayMock
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

        return $this->json->unserialize($response->getContent());
    }

    /**
     * Get saved cart data
     *
     * @return PaymentTokenInterface
     */
    private function getVaultCartData()
    {
        /** @var PaymentTokenManagement $tokenManagement */
        $tokenManagement = $this->objectManager->get(PaymentTokenManagement::class);
        $token = $tokenManagement->getByGatewayToken(
            'B70CCC236815',
            'payflowpro',
            1
        );
        /** @var PaymentTokenRepository $tokenRepository */
        $tokenRepository = $this->objectManager->get(PaymentTokenRepository::class);
        return $tokenRepository->getById($token->getEntityId());
    }
}
