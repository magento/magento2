<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Guest;

use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for setting offline shipping methods on cart
 */
class SetOfflineShippingMethodsOnCartTest extends GraphQlAbstract
{
    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/enable_offline_shipping_methods.php
     * @magentoApiDataFixture Magento/OfflineShipping/_files/tablerates_weight.php
     *
     * @param string $carrierCode
     * @param string $methodCode
     * @param float $amount
     * @param string $label
     * @dataProvider offlineShippingMethodDataProvider
     */
    public function testSetOfflineShippingMethod(string $carrierCode, string $methodCode, float $amount, string $label)
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = $this->getQuery(
            $maskedQuoteId,
            $methodCode,
            $carrierCode
        );
        $response = $this->graphQlMutation($query);

        self::assertArrayHasKey('setShippingMethodsOnCart', $response);
        self::assertArrayHasKey('cart', $response['setShippingMethodsOnCart']);
        self::assertArrayHasKey('shipping_addresses', $response['setShippingMethodsOnCart']['cart']);
        self::assertCount(1, $response['setShippingMethodsOnCart']['cart']['shipping_addresses']);

        $shippingAddress = current($response['setShippingMethodsOnCart']['cart']['shipping_addresses']);
        self::assertArrayHasKey('selected_shipping_method', $shippingAddress);

        self::assertArrayHasKey('carrier_code', $shippingAddress['selected_shipping_method']);
        self::assertEquals($carrierCode, $shippingAddress['selected_shipping_method']['carrier_code']);

        self::assertArrayHasKey('method_code', $shippingAddress['selected_shipping_method']);
        self::assertEquals($methodCode, $shippingAddress['selected_shipping_method']['method_code']);

        self::assertArrayHasKey('amount', $shippingAddress['selected_shipping_method']);
        self::assertEquals($amount, $shippingAddress['selected_shipping_method']['amount']);

        self::assertArrayHasKey('label', $shippingAddress['selected_shipping_method']);
        self::assertEquals($label, $shippingAddress['selected_shipping_method']['label']);
    }

    /**
     * @return array
     */
    public function offlineShippingMethodDataProvider(): array
    {
        return [
            'flatrate_flatrate' => ['flatrate', 'flatrate', 10, 'Flat Rate - Fixed'],
            'tablerate_bestway' => ['tablerate', 'bestway', 10, 'Best Way - Table Rate'],
            'freeshipping_freeshipping' => ['freeshipping', 'freeshipping', 0, 'Free Shipping - Free'],
        ];
    }

    /**
     * @param string $maskedQuoteId
     * @param string $shippingMethodCode
     * @param string $shippingCarrierCode
     * @return string
     */
    private function getQuery(
        string $maskedQuoteId,
        string $shippingMethodCode,
        string $shippingCarrierCode
    ): string {
        return <<<QUERY
mutation {
  setShippingMethodsOnCart(input: 
    {
      cart_id: "$maskedQuoteId", 
      shipping_methods: [{
        carrier_code: "$shippingCarrierCode"
        method_code: "$shippingMethodCode"
      }]
    }) {
    cart {
      shipping_addresses {
        selected_shipping_method {
          carrier_code
          method_code
          amount
          label
        }
      }
    }
  }
}
QUERY;
    }
}
