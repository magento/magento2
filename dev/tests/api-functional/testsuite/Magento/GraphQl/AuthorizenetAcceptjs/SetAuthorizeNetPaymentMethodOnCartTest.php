<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\AuthorizenetAcceptjs;

use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for authorizeNet payment methods on cart by guest and customer
 */
class SetAuthorizeNetPaymentMethodOnCartTest extends GraphQlAbstract
{

    /** @var  CustomerTokenServiceInterface */
    private $customerTokenService;
    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    /**
     * @var string
     */
   // private $authorizenetStatusPath = 'payment/authorizenet_acceptjs/active';

    /**
     * @var string
     */
   // private $authorizenetEnvironmentPath = 'payment/authorizenet_acceptjs/environment';

    /**
     * @var string
     */
    //private $authorizenetLoginPath = 'payment/authorizenet_acceptjs/login';

    /**
     * @var string
     */
    //private $authorizenetTransactionKeyPath = 'payment/authorizenet_acceptjs/trans_key';

    /**
     * @var string
     */
    //private $authorizenetTransSignatureKeyPath = 'payment/authorizenet_acceptjs/trans_signature_key';

    /**
     * @var string
     */
   // private $authorizenetPublicClientKeyPath = 'payment/authorizenet_acceptjs/public_client_key';

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
        /** @var \Magento\Config\Model\ResourceModel\Config $config */
        /*$config = $objectManager->get(\Magento\Config\Model\ResourceModel\Config::class);
        $config->saveConfig($this->authorizenetStatusPath, 1, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
        $config->saveConfig($this->authorizenetLoginPath, 'someusername', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
        $config->saveConfig(
            $this->authorizenetTransactionKeyPath,
            'somepassword',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );
        $config->saveConfig(
            $this->authorizenetTransSignatureKeyPath,
            'abc',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );
        $config->saveConfig($this->authorizenetPublicClientKeyPath, 'xyz', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);*/
        /** @var ReinitableConfigInterface $config */
       // $config =$objectManager->get(ReinitableConfigInterface::class);
       // $config->reinit();
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
    }

    private function resetAuthorizeNetConfig() : void
    {
      //  $objectManager = Bootstrap::getObjectManager();
        /** @var \Magento\Config\Model\ResourceModel\Config $config */
        /*$config = $objectManager->get(\Magento\Config\Model\ResourceModel\Config::class);
        $config->deleteConfig($this->authorizenetStatusPath, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
        $config->deleteConfig($this->authorizenetEnvironmentPath, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
        $config->deleteConfig($this->authorizenetLoginPath, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
        $config->deleteConfig($this->authorizenetTransactionKeyPath, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
        $config->deleteConfig($this->authorizenetTransSignatureKeyPath, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
        $config->deleteConfig($this->authorizenetPublicClientKeyPath, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);*/
    }

    /**
     * Test for setting Authorizenet payment method on cart for guest
     *
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_canada_address.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/enable_offline_payment_methods.php
     */
    public function testSetAuthorizeNetPaymentOnCartForGuest()
    {
        $maskedQuoteIdForGuest = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $methodCode = 'authorizenet_acceptjs';
        $query = $this->getSetPaymentMethodQuery($maskedQuoteIdForGuest, $methodCode);

        $response = $this->graphQlMutation($query);
        self::assertArrayHasKey('setPaymentMethodOnCart', $response);
        self::assertArrayHasKey('cart', $response['setPaymentMethodOnCart']);
        self::assertArrayHasKey('selected_payment_method', $response['setPaymentMethodOnCart']['cart']);
        $selectedPaymentMethod = $response['setPaymentMethodOnCart']['cart']['selected_payment_method'];
        self::assertArrayHasKey('code', $selectedPaymentMethod);
    }

    /**
     * Test for setting Authorizenet payment method on cart for customer
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/enable_offline_payment_methods.php
     */
    public function testSetAuthorizeNetPaymentOnCartForRegisteredCustomer()
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var GetMaskedQuoteIdByReservedOrderId $getMaskedQuoteIdByReservedOrderIdForCustomer */
        $getMaskedQuoteIdByReservedOrderIdForCustomer = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
        $maskedQuoteId = $getMaskedQuoteIdByReservedOrderIdForCustomer->execute('test_quote');
        $methodCode = 'authorizenet_acceptjs';
        $query = $this->getSetPaymentMethodQuery($maskedQuoteId, $methodCode);
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());

        self::assertArrayHasKey('setPaymentMethodOnCart', $response);
        self::assertArrayHasKey('cart', $response['setPaymentMethodOnCart']);
        self::assertArrayHasKey('selected_payment_method', $response['setPaymentMethodOnCart']['cart']);
        $selectedPaymentMethod = $response['setPaymentMethodOnCart']['cart']['selected_payment_method'];
        self::assertArrayHasKey('code', $selectedPaymentMethod);
    }

    /**
     * Get the setPaymentMethod mutation query
     *
     * @param string $maskedQuoteId
     * @param string $methodCode
     * @return string
     */
    private function getSetPaymentMethodQuery(string $maskedQuoteId, string $methodCode) : string
    {
        return <<<QUERY
    mutation {
    setPaymentMethodOnCart(
        input: {
            cart_id: "$maskedQuoteId",
            payment_method: {
                code:"$methodCode",
                additional_data: {
                    authorizenet_acceptjs: {
                        opaque_data_descriptor:
                         "COMMON.ACCEPT.INAPP.PAYMENT",
                         opaque_data_value: "abx",
                         cc_last_4: 1111
                         }
                        }
                       }
                      }
                     ) {
                        cart {
                            selected_payment_method { 
                            code
                            } items {product {sku}}}}}
QUERY;
    }

    public function tearDown()
    {
        $this->resetAuthorizeNetConfig();
    }

    /**
     *  Create a header map for generating customer token
     *
     * @param string $username
     * @param string $password
     * @return array
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    private function getHeaderMap(string $username = 'customer@example.com', string $password = 'password'): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($username, $password);
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];
        return $headerMap;
    }
}
