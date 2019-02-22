<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\OfflinePayments\Model\Banktransfer;
use Magento\OfflinePayments\Model\Cashondelivery;
use Magento\OfflinePayments\Model\Checkmo;
use Magento\OfflinePayments\Model\Purchaseorder;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Config\Model\ResourceModel\Config;

/**
 * Test for setting payment methods on cart
 */
class SetPaymentMethodOnCartTest extends GraphQlAbstract
{
    private const OFFLINE_METHOD_CODES = [
        Checkmo::PAYMENT_METHOD_CHECKMO_CODE,
        Banktransfer::PAYMENT_METHOD_BANKTRANSFER_CODE,
        Cashondelivery::PAYMENT_METHOD_CASHONDELIVERY_CODE,
        Purchaseorder::PAYMENT_METHOD_PURCHASEORDER_CODE,
    ];

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @var Quote
     */
    private $quote;

    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedId;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var TypeListInterface
     */
    private $cacheList;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->quoteResource = $objectManager->create(QuoteResource::class);
        $this->quote = $objectManager->create(Quote::class);
        $this->quoteIdToMaskedId = $objectManager->create(QuoteIdToMaskedQuoteIdInterface::class);
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
        $this->config = $objectManager->get(Config::class);
        $this->cacheList = $objectManager->get(TypeListInterface::class);

        foreach (static::OFFLINE_METHOD_CODES as $offlineMethodCode) {
            $this->config->saveConfig(
                'payment/' . $offlineMethodCode . '/active',
                '1',
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                0
            );
        }
        $this->cacheList->cleanType('config');
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        foreach (static::OFFLINE_METHOD_CODES as $offlineMethodCode) {
            //Never no disable checkmo method
            if ($offlineMethodCode === Checkmo::PAYMENT_METHOD_CHECKMO_CODE) {
                continue;
            }
            $this->config->saveConfig(
                'payment/' . $offlineMethodCode . '/active',
                '0',
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                0
            );
        }
        $this->cacheList->cleanType('config');
    }

    /**
     * @param string $methodCode
     * @dataProvider dataProviderOfflinePaymentMethods
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     */
    public function testSetPaymentMethodOnCart(string $methodCode)
    {
        /** @var \Magento\Config\Model\ResourceModel\Config $config */
        $config = ObjectManager::getInstance()->get(\Magento\Config\Model\ResourceModel\Config::class);
        $config->saveConfig(
            'payment/' . $methodCode . '/active',
            1,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );

        $this->quoteResource->load(
            $this->quote,
            'test_order_1',
            'reserved_order_id'
        );
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());

        $query = $this->prepareMutationQuery(
            $maskedQuoteId,
            $methodCode
        );

        $response = $this->sendRequestWithToken($query);

        $this->assertArrayHasKey('setPaymentMethodOnCart', $response);
        $this->assertArrayHasKey('cart', $response['setPaymentMethodOnCart']);
        $this->assertEquals($maskedQuoteId, $response['setPaymentMethodOnCart']['cart']['cart_id']);
        $this->assertArrayHasKey('payment_method', $response['setPaymentMethodOnCart']['cart']);
        $this->assertEquals($methodCode, $response['setPaymentMethodOnCart']['cart']['payment_method']['code']);
    }

    public function dataProviderOfflinePaymentMethods(): array
    {
        $methods = [];
        foreach (static::OFFLINE_METHOD_CODES as $offlineMethodCode) {
            //Purchase order requires additional input and is tested separately
            if ($offlineMethodCode === Purchaseorder::PAYMENT_METHOD_PURCHASEORDER_CODE) {
                continue;
            }
            $methods[] = [$offlineMethodCode];
        }

        return $methods;
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     */
    public function testSetNonExistingPaymentMethod()
    {
        $paymentMethod = 'noway';
        $this->quoteResource->load(
            $this->quote,
            'test_order_1',
            'reserved_order_id'
        );
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());

        $query = $this->prepareMutationQuery(
            $maskedQuoteId,
            $paymentMethod
        );

        $this->expectExceptionMessage('The requested Payment Method is not available.');
        $this->sendRequestWithToken($query);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     */
    public function testSetPaymentMethodByGuestToCustomerCart()
    {
        $paymentMethod = 'checkmo';
        $this->quoteResource->load(
            $this->quote,
            'test_order_1',
            'reserved_order_id'
        );
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());

        $query = $this->prepareMutationQuery(
            $maskedQuoteId,
            $paymentMethod
        );

        $this->expectExceptionMessage(
            "The current user cannot perform operations on cart \"$maskedQuoteId\""
        );

        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     */
    public function testSetPaymentMethodPurchaseOrderOnCart()
    {
        $methodCode = \Magento\OfflinePayments\Model\Purchaseorder::PAYMENT_METHOD_PURCHASEORDER_CODE;
        $poNumber = 'GQL-19002';

        /** @var \Magento\Config\Model\ResourceModel\Config $config */
        $config = ObjectManager::getInstance()->get(\Magento\Config\Model\ResourceModel\Config::class);
        $config->saveConfig(
            'payment/' . $methodCode . '/active',
            1,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );

        $this->quoteResource->load(
            $this->quote,
            'test_order_1',
            'reserved_order_id'
        );
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());

        $query = <<<QUERY
mutation {
  setPaymentMethodOnCart(input: 
    {
      cart_id: "$maskedQuoteId", 
      payment_method: {
          code: "$methodCode"
          po_number: "$poNumber"
        }
      }) {
    
    cart {
      cart_id,
      payment_method {
        code
        po_number
      }
    }
  }
}

QUERY;

        $response = $this->sendRequestWithToken($query);

        $this->assertArrayHasKey('setPaymentMethodOnCart', $response);
        $this->assertArrayHasKey('cart', $response['setPaymentMethodOnCart']);
        $this->assertEquals($maskedQuoteId, $response['setPaymentMethodOnCart']['cart']['cart_id']);
        $this->assertArrayHasKey('payment_method', $response['setPaymentMethodOnCart']['cart']);
        $this->assertEquals($methodCode, $response['setPaymentMethodOnCart']['cart']['payment_method']['code']);
        $this->assertEquals($poNumber, $response['setPaymentMethodOnCart']['cart']['payment_method']['po_number']);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     */
    public function testPurchaseOrderPaymentMethodFailingValidation()
    {
        $this->quoteResource->load(
            $this->quote,
            'test_order_1',
            'reserved_order_id'
        );
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());

        $query = $this->prepareMutationQuery(
            $maskedQuoteId,
            Purchaseorder::PAYMENT_METHOD_PURCHASEORDER_CODE
        );

        $this->expectExceptionMessage('Purchase order number is a required field.');
        $this->sendRequestWithToken($query);
    }

    /**
     * Generates query for setting the specified shipping method on cart
     *
     * @param string $maskedQuoteId
     * @param string $methodCode
     * @return string
     */
    private function prepareMutationQuery(
        string $maskedQuoteId,
        string $methodCode
    ) : string {
        return <<<QUERY
mutation {
  setPaymentMethodOnCart(input: 
    {
      cart_id: "$maskedQuoteId", 
      payment_method: {
          code: "$methodCode"
        }
      }) {
    
    cart {
      cart_id,
      payment_method {
        code
      }
    }
  }
}

QUERY;
    }

    /**
     * Sends a GraphQL request with using a bearer token
     *
     * @param string $query
     * @return array
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    private function sendRequestWithToken(string $query): array
    {

        $customerToken = $this->customerTokenService->createCustomerAccessToken('customer@example.com', 'password');
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];

        return $this->graphQlQuery($query, [], '', $headerMap);
    }
}
