<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalGraphQl\Model\Resolver\Customer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\Data\ShippingInformationInterfaceFactory;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Integration\Model\Oauth\Token;
use Magento\PaypalGraphQl\PaypalPayflowProAbstractTest;
use Magento\Quote\Api\BillingAddressManagementInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\QuoteIdToMaskedQuoteId;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\Quote\Model\ShippingAddressManagementInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\PaymentTokenManagement;
use Magento\Vault\Model\PaymentTokenRepository;

/**
 * End to end place order test using payflowpro_cc_vault via graphql endpoint for customer
 *
 * @magentoAppArea graphql
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PlaceOrderWithPayflowProCCVaultTest extends PaypalPayflowProAbstractTest
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
    public function testPlaceOrderWithCCVault(): void
    {
        $this->placeOrderPayflowPro('is_active_payment_token_enabler: true');
        $publicHash = $this->getVaultCartData()->getPublicHash();
        /** @var CartManagementInterface $cartManagement */
        $cartManagement = $this->objectManager->get(CartManagementInterface::class);
        /** @var CartRepositoryInterface $cartRepository */
        $cartRepository = $this->objectManager->get(CartRepositoryInterface::class);
        /** @var QuoteIdMaskFactory $quoteIdMaskFactory */
        $quoteIdMaskFactory = $this->objectManager->get(QuoteIdMaskFactory::class);
        $cartId = $cartManagement->createEmptyCartForCustomer(1);
        $cart = $cartRepository->get($cartId);
        $cart->setReservedOrderId('test_quote_1');
        $cartRepository->save($cart);
        /** @var QuoteIdMask $quoteIdMask */
        $quoteIdMask = $quoteIdMaskFactory->create();
        $quoteIdMask->setQuoteId($cartId)
            ->save();

        $reservedQuoteId = 'test_quote_1';
        $cart = $this->getQuoteByReservedOrderId($reservedQuoteId);
        $cartId = $this->quoteIdToMaskedId->execute((int)$cart->getId());

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        /** @var QuoteFactory $quoteFactory */
        $quoteFactory = $this->objectManager->get(QuoteFactory::class);
        /** @var QuoteResource $quoteResource */
        $quoteResource = $this->objectManager->get(QuoteResource::class);
        $product = $productRepository->get('simple_product');
        $quote = $quoteFactory->create();
        $quoteResource->load($quote, 'test_quote_1', 'reserved_order_id');
        $quote->addProduct($product, 2);
        $cartRepository->save($quote);

        /** @var AddressInterfaceFactory $quoteAddressFactory */
        $quoteAddressFactory = $this->objectManager->get(AddressInterfaceFactory::class);
        /** @var DataObjectHelper $dataObjectHelper */
        $dataObjectHelper = $this->objectManager->get(DataObjectHelper::class);
        /** @var ShippingAddressManagementInterface $shippingAddressManagement */
        $shippingAddressManagement = $this->objectManager->get(ShippingAddressManagementInterface::class);

        $quoteAddressData = [
            AddressInterface::KEY_TELEPHONE => 3468676,
            AddressInterface::KEY_POSTCODE => '75477',
            AddressInterface::KEY_COUNTRY_ID => 'US',
            AddressInterface::KEY_CITY => 'CityM',
            AddressInterface::KEY_COMPANY => 'CompanyName',
            AddressInterface::KEY_STREET => 'Green str, 67',
            AddressInterface::KEY_LASTNAME => 'Smith',
            AddressInterface::KEY_FIRSTNAME => 'John',
            AddressInterface::KEY_REGION_ID => 1,
        ];
        $quoteAddress = $quoteAddressFactory->create();
        $dataObjectHelper->populateWithArray($quoteAddress, $quoteAddressData, AddressInterfaceFactory::class);

        $quote = $quoteFactory->create();
        $quoteResource->load($quote, 'test_quote_1', 'reserved_order_id');
        $shippingAddressManagement->assign($quote->getId(), $quoteAddress);

        /** @var BillingAddressManagementInterface $billingAddressManagement */
        $billingAddressManagement = $this->objectManager->get(BillingAddressManagementInterface::class);
        $billingAddressManagement->assign($quote->getId(), $quoteAddress);

        /** @var ShippingInformationInterfaceFactory $shippingInformationFactory */
        $shippingInformationFactory = $this->objectManager->get(ShippingInformationInterfaceFactory::class);
        /** @var ShippingInformationManagementInterface $shippingInformationManagement */
        $shippingInformationManagement = $this->objectManager->get(ShippingInformationManagementInterface::class);
        $quoteAddress = $quote->getShippingAddress();

        /** @var ShippingInformationInterface $shippingInformation */
        $shippingInformation = $shippingInformationFactory->create([
            'data' => [
                ShippingInformationInterface::SHIPPING_ADDRESS => $quoteAddress,
                ShippingInformationInterface::SHIPPING_CARRIER_CODE => 'flatrate',
                ShippingInformationInterface::SHIPPING_METHOD_CODE => 'flatrate',
            ],
        ]);
        $shippingInformationManagement->saveAddressInformation($quote->getId(), $shippingInformation);

        $secondQuery = <<<QUERY
mutation {
setPaymentMethodOnCart(input: {
payment_method: {
  code: "payflowpro_cc_vault",
    payflowpro_cc_vault: {
      public_hash:"{$publicHash}"
  }
},
cart_id: "{$cartId}"})
{
cart {
  selected_payment_method {code}
 }
}
placeOrder(input: {cart_id: "{$cartId}"}) {
order {order_number}
 }
}
QUERY;
        /** @var CustomerTokenServiceInterface $tokenService */
        $tokenService = $this->objectManager->get(CustomerTokenServiceInterface::class);
        $customerToken = $tokenService->createCustomerAccessToken('customer@example.com', 'password');

        $requestHeaders = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $customerToken
        ];
        $vaultResponse = $this->graphQlRequest->send($secondQuery, [], '', $requestHeaders);

        $responseData =  $this->json->unserialize($vaultResponse->getContent());
        $this->assertArrayHasKey('data', $responseData);
        $this->assertTrue(
            isset($responseData['data']['setPaymentMethodOnCart']['cart']['selected_payment_method']['code'])
        );
        $this->assertEquals(
            'payflowpro_cc_vault',
            $responseData['data']['setPaymentMethodOnCart']['cart']['selected_payment_method']['code']
        );
        $this->assertTrue(
            isset($responseData['data']['placeOrder']['order']['order_number'])
        );
        $this->assertEquals(
            'test_quote_1',
            $responseData['data']['placeOrder']['order']['order_number']
        );
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

        /** @var CustomerTokenServiceInterface $tokenService */
        $tokenService = $this->objectManager->get(CustomerTokenServiceInterface::class);
        $customerToken = $tokenService->createCustomerAccessToken('customer@example.com', 'password');

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
