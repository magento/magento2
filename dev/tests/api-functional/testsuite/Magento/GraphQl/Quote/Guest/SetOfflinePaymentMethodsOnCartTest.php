<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Guest;

use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\OfflinePayments\Model\Banktransfer;
use Magento\OfflinePayments\Model\Cashondelivery;
use Magento\OfflinePayments\Model\Checkmo;
use Magento\OfflinePayments\Model\Purchaseorder;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for setting offline payment methods on cart
 */
class SetOfflinePaymentMethodsOnCartTest extends GraphQlAbstract
{
    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoConfigFixture default_store payment/banktransfer/active 1
     * @magentoConfigFixture default_store payment/cashondelivery/active 1
     * @magentoConfigFixture default_store payment/checkmo/active 1
     * @magentoConfigFixture default_store payment/purchaseorder/active 1
     *
     * @param string $methodCode
     * @param string $methodTitle
     * @dataProvider offlinePaymentMethodDataProvider
     */
    public function testSetOfflinePaymentMethod(string $methodCode, string $methodTitle)
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = $this->getQuery($maskedQuoteId, $methodCode);
        $response = $this->graphQlMutation($query);

        self::assertArrayHasKey('setPaymentMethodOnCart', $response);
        self::assertArrayHasKey('cart', $response['setPaymentMethodOnCart']);
        self::assertArrayHasKey('selected_payment_method', $response['setPaymentMethodOnCart']['cart']);

        $selectedPaymentMethod = $response['setPaymentMethodOnCart']['cart']['selected_payment_method'];
        self::assertArrayHasKey('code', $selectedPaymentMethod);
        self::assertEquals($methodCode, $selectedPaymentMethod['code']);

        self::assertArrayHasKey('title', $selectedPaymentMethod);
        self::assertEquals($methodTitle, $selectedPaymentMethod['title']);
    }

    /**
     * @return array
     */
    public function offlinePaymentMethodDataProvider(): array
    {
        return [
            'check_mo' => [Checkmo::PAYMENT_METHOD_CHECKMO_CODE, 'Check / Money order'],
            'bank_transfer' => [Banktransfer::PAYMENT_METHOD_BANKTRANSFER_CODE, 'Bank Transfer Payment'],
            'cash_on_delivery' => [Cashondelivery::PAYMENT_METHOD_CASHONDELIVERY_CODE, 'Cash On Delivery'],
        ];
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoConfigFixture default_store payment/banktransfer/active 1
     * @magentoConfigFixture default_store payment/cashondelivery/active 1
     * @magentoConfigFixture default_store payment/checkmo/active 1
     * @magentoConfigFixture default_store payment/purchaseorder/active 1
     */
    public function testSetPurchaseOrderPaymentMethod()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $methodCode = Purchaseorder::PAYMENT_METHOD_PURCHASEORDER_CODE;
        $methodTitle = 'Purchase Order';
        $poNumber = 'abc123';

        $query = <<<QUERY
mutation {
  setPaymentMethodOnCart(input: {
    cart_id: "{$maskedQuoteId}",
    payment_method: {
      code: "{$methodCode}"
      purchase_order_number: "{$poNumber}"
    }
  }) {
    cart {
      selected_payment_method {
        code
        title
        purchase_order_number
      }
    }
  }
}
QUERY;

        $response = $this->graphQlMutation($query);

        self::assertArrayHasKey('setPaymentMethodOnCart', $response);
        self::assertArrayHasKey('cart', $response['setPaymentMethodOnCart']);
        self::assertArrayHasKey('selected_payment_method', $response['setPaymentMethodOnCart']['cart']);

        $selectedPaymentMethod = $response['setPaymentMethodOnCart']['cart']['selected_payment_method'];
        self::assertArrayHasKey('code', $selectedPaymentMethod);
        self::assertEquals($methodCode, $selectedPaymentMethod['code']);

        self::assertArrayHasKey('title', $selectedPaymentMethod);
        self::assertEquals($methodTitle, $selectedPaymentMethod['title']);

        self::assertArrayHasKey('purchase_order_number', $selectedPaymentMethod);
        self::assertEquals($poNumber, $selectedPaymentMethod['purchase_order_number']);
    }

    /**
     * @param string $maskedQuoteId
     * @param string $methodCode
     * @return string
     */
    private function getQuery(
        string $maskedQuoteId,
        string $methodCode
    ) : string {
        return <<<QUERY
mutation {
  setPaymentMethodOnCart(input: {
    cart_id: "{$maskedQuoteId}",
    payment_method: {
      code: "{$methodCode}"
    }
  }) {
    cart {
      selected_payment_method {
        code
        title
      }
    }
  }
}
QUERY;
    }
}
