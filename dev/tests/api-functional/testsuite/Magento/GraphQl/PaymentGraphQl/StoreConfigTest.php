<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\PaymentGraphQl;

use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test coverage for zero subtotal and check/money order payment methods in the store config
 *
 */
class StoreConfigTest extends GraphQlAbstract
{
    public const STORE_CONFIG_QUERY = <<<QUERY
{
    storeConfig {
        zero_subtotal_enabled
        zero_subtotal_title
        zero_subtotal_new_order_status
        zero_subtotal_payment_action
        zero_subtotal_enable_for_specific_countries
        zero_subtotal_payment_from_specific_countries
        zero_subtotal_sort_order
        check_money_order_enabled
        check_money_order_title
        check_money_order_new_order_status
        check_money_order_enable_for_specific_countries
        check_money_order_payment_from_specific_countries
        check_money_order_make_check_payable_to
        check_money_order_send_check_to
        check_money_order_min_order_total
        check_money_order_max_order_total
        check_money_order_sort_order
    }
}
QUERY;

    /**
     * Test that storeConfig is correct for default configuration values.
     *
     * @throws \Exception
     */
    public function testStoreConfigZeroSubtotalCheckMoneyOrderDefaultValues()
    {
        $response = $this->graphQlQuery(self::STORE_CONFIG_QUERY);
        self::assertArrayHasKey('zero_subtotal_enabled', $response['storeConfig']);
        self::assertArrayHasKey('zero_subtotal_title', $response['storeConfig']);
        self::assertArrayHasKey('zero_subtotal_new_order_status', $response['storeConfig']);
        self::assertArrayHasKey('zero_subtotal_payment_action', $response['storeConfig']);
        self::assertArrayHasKey('zero_subtotal_enable_for_specific_countries', $response['storeConfig']);
        self::assertArrayHasKey('zero_subtotal_payment_from_specific_countries', $response['storeConfig']);
        self::assertArrayHasKey('zero_subtotal_sort_order', $response['storeConfig']);
        self::assertArrayHasKey('check_money_order_enabled', $response['storeConfig']);
        self::assertArrayHasKey('check_money_order_title', $response['storeConfig']);
        self::assertArrayHasKey('check_money_order_new_order_status', $response['storeConfig']);
        self::assertArrayHasKey('check_money_order_enable_for_specific_countries', $response['storeConfig']);
        self::assertArrayHasKey('check_money_order_payment_from_specific_countries', $response['storeConfig']);
        self::assertArrayHasKey('check_money_order_make_check_payable_to', $response['storeConfig']);
        self::assertArrayHasKey('check_money_order_send_check_to', $response['storeConfig']);
        self::assertArrayHasKey('check_money_order_min_order_total', $response['storeConfig']);
        self::assertArrayHasKey('check_money_order_max_order_total', $response['storeConfig']);
        self::assertArrayHasKey('check_money_order_sort_order', $response['storeConfig']);

        self::assertTrue($response['storeConfig']['zero_subtotal_enabled']);
        self::assertEquals('No Payment Information Required', $response['storeConfig']['zero_subtotal_title']);
        self::assertEquals('pending', $response['storeConfig']['zero_subtotal_new_order_status']);
        self::assertEquals('authorize_capture', $response['storeConfig']['zero_subtotal_payment_action']);
        self::assertFalse($response['storeConfig']['zero_subtotal_enable_for_specific_countries']);
        self::assertNull($response['storeConfig']['zero_subtotal_payment_from_specific_countries']);
        self::assertEquals(1, $response['storeConfig']['zero_subtotal_sort_order']);
        self::assertTrue($response['storeConfig']['check_money_order_enabled']);
        self::assertEquals('Check / Money order', $response['storeConfig']['check_money_order_title']);
        self::assertEquals('pending', $response['storeConfig']['check_money_order_new_order_status']);
        self::assertFalse($response['storeConfig']['check_money_order_enable_for_specific_countries']);
        self::assertNull($response['storeConfig']['check_money_order_payment_from_specific_countries']);
        self::assertNull($response['storeConfig']['check_money_order_make_check_payable_to']);
        self::assertNull($response['storeConfig']['check_money_order_send_check_to']);
        self::assertNull($response['storeConfig']['check_money_order_min_order_total']);
        self::assertNull($response['storeConfig']['check_money_order_max_order_total']);
        self::assertNull($response['storeConfig']['check_money_order_sort_order']);
    }

    /**
     * Test that storeConfig is correct when zero subtotal and check/money order payment methods are disabled.
     *
     * @magentoConfigFixture default/payment/free/active 0
     * @magentoConfigFixture default/payment/checkmo/active 0
     *
     * @throws \Exception
     */
    public function testStoreConfigZeroSubtotalCheckMoneyOrderDisabled()
    {
        $response = $this->graphQlQuery(self::STORE_CONFIG_QUERY);
        self::assertArrayHasKey('zero_subtotal_enabled', $response['storeConfig']);
        self::assertArrayHasKey('zero_subtotal_title', $response['storeConfig']);
        self::assertArrayHasKey('zero_subtotal_new_order_status', $response['storeConfig']);
        self::assertArrayHasKey('zero_subtotal_payment_action', $response['storeConfig']);
        self::assertArrayHasKey('zero_subtotal_enable_for_specific_countries', $response['storeConfig']);
        self::assertArrayHasKey('zero_subtotal_payment_from_specific_countries', $response['storeConfig']);
        self::assertArrayHasKey('zero_subtotal_sort_order', $response['storeConfig']);
        self::assertArrayHasKey('check_money_order_enabled', $response['storeConfig']);
        self::assertArrayHasKey('check_money_order_title', $response['storeConfig']);
        self::assertArrayHasKey('check_money_order_new_order_status', $response['storeConfig']);
        self::assertArrayHasKey('check_money_order_enable_for_specific_countries', $response['storeConfig']);
        self::assertArrayHasKey('check_money_order_payment_from_specific_countries', $response['storeConfig']);
        self::assertArrayHasKey('check_money_order_make_check_payable_to', $response['storeConfig']);
        self::assertArrayHasKey('check_money_order_send_check_to', $response['storeConfig']);
        self::assertArrayHasKey('check_money_order_min_order_total', $response['storeConfig']);
        self::assertArrayHasKey('check_money_order_max_order_total', $response['storeConfig']);
        self::assertArrayHasKey('check_money_order_sort_order', $response['storeConfig']);

        self::assertFalse($response['storeConfig']['zero_subtotal_enabled']);
        self::assertEquals('No Payment Information Required', $response['storeConfig']['zero_subtotal_title']);
        self::assertEquals('pending', $response['storeConfig']['zero_subtotal_new_order_status']);
        self::assertEquals('authorize_capture', $response['storeConfig']['zero_subtotal_payment_action']);
        self::assertFalse($response['storeConfig']['zero_subtotal_enable_for_specific_countries']);
        self::assertNull($response['storeConfig']['zero_subtotal_payment_from_specific_countries']);
        self::assertEquals(1, $response['storeConfig']['zero_subtotal_sort_order']);
        self::assertFalse($response['storeConfig']['check_money_order_enabled']);
        self::assertEquals('Check / Money order', $response['storeConfig']['check_money_order_title']);
        self::assertEquals('pending', $response['storeConfig']['check_money_order_new_order_status']);
        self::assertFalse($response['storeConfig']['check_money_order_enable_for_specific_countries']);
        self::assertNull($response['storeConfig']['check_money_order_payment_from_specific_countries']);
        self::assertNull($response['storeConfig']['check_money_order_make_check_payable_to']);
        self::assertNull($response['storeConfig']['check_money_order_send_check_to']);
        self::assertNull($response['storeConfig']['check_money_order_min_order_total']);
        self::assertNull($response['storeConfig']['check_money_order_max_order_total']);
        self::assertNull($response['storeConfig']['check_money_order_sort_order']);
    }

    /**
     * Test that storeConfig is correct for custom values.
     *
     * @magentoConfigFixture default/payment/free/title Test Zero Subtotal Title
     * @magentoConfigFixture default/payment/free/order_status processing
     * @magentoConfigFixture default/payment/free/allowspecific 1
     * @magentoConfigFixture default/payment/free/specificcountry DZ
     * @magentoConfigFixture default/payment/free/sort_order 5
     * @magentoConfigFixture default/payment/checkmo/title Test Check / Money Order Title
     * @magentoConfigFixture default/payment/checkmo/allowspecific 1
     * @magentoConfigFixture default/payment/checkmo/specificcountry BR
     * @magentoConfigFixture default/payment/checkmo/payable_to Test Payee
     * @magentoConfigFixture default/payment/checkmo/mailing_address Test Address
     * @magentoConfigFixture default/payment/checkmo/min_order_total 5.00
     * @magentoConfigFixture default/payment/checkmo/max_order_total 5555.00
     * @magentoConfigFixture default/payment/checkmo/sort_order 7
     *
     * @throws \Exception
     */
    public function testStoreConfigZeroSubtotalCheckMoneyOrderCustom()
    {
        $response = $this->graphQlQuery(self::STORE_CONFIG_QUERY);
        self::assertArrayHasKey('zero_subtotal_enabled', $response['storeConfig']);
        self::assertArrayHasKey('zero_subtotal_title', $response['storeConfig']);
        self::assertArrayHasKey('zero_subtotal_new_order_status', $response['storeConfig']);
        self::assertArrayHasKey('zero_subtotal_payment_action', $response['storeConfig']);
        self::assertArrayHasKey('zero_subtotal_enable_for_specific_countries', $response['storeConfig']);
        self::assertArrayHasKey('zero_subtotal_payment_from_specific_countries', $response['storeConfig']);
        self::assertArrayHasKey('zero_subtotal_sort_order', $response['storeConfig']);
        self::assertArrayHasKey('check_money_order_enabled', $response['storeConfig']);
        self::assertArrayHasKey('check_money_order_title', $response['storeConfig']);
        self::assertArrayHasKey('check_money_order_new_order_status', $response['storeConfig']);
        self::assertArrayHasKey('check_money_order_enable_for_specific_countries', $response['storeConfig']);
        self::assertArrayHasKey('check_money_order_payment_from_specific_countries', $response['storeConfig']);
        self::assertArrayHasKey('check_money_order_make_check_payable_to', $response['storeConfig']);
        self::assertArrayHasKey('check_money_order_send_check_to', $response['storeConfig']);
        self::assertArrayHasKey('check_money_order_min_order_total', $response['storeConfig']);
        self::assertArrayHasKey('check_money_order_max_order_total', $response['storeConfig']);
        self::assertArrayHasKey('check_money_order_sort_order', $response['storeConfig']);

        self::assertTrue($response['storeConfig']['zero_subtotal_enabled']);
        self::assertEquals('Test Zero Subtotal Title', $response['storeConfig']['zero_subtotal_title']);
        self::assertEquals('processing', $response['storeConfig']['zero_subtotal_new_order_status']);
        self::assertEquals('authorize_capture', $response['storeConfig']['zero_subtotal_payment_action']);
        self::assertTrue($response['storeConfig']['zero_subtotal_enable_for_specific_countries']);
        self::assertEquals('DZ', $response['storeConfig']['zero_subtotal_payment_from_specific_countries']);
        self::assertEquals(5, $response['storeConfig']['zero_subtotal_sort_order']);
        self::assertTrue($response['storeConfig']['check_money_order_enabled']);
        self::assertEquals('Test Check / Money Order Title', $response['storeConfig']['check_money_order_title']);
        self::assertEquals('pending', $response['storeConfig']['check_money_order_new_order_status']);
        self::assertTrue($response['storeConfig']['check_money_order_enable_for_specific_countries']);
        self::assertEquals('BR', $response['storeConfig']['check_money_order_payment_from_specific_countries']);
        self::assertEquals('Test Payee', $response['storeConfig']['check_money_order_make_check_payable_to']);
        self::assertEquals('Test Address', $response['storeConfig']['check_money_order_send_check_to']);
        self::assertEquals('5.00', $response['storeConfig']['check_money_order_min_order_total']);
        self::assertEquals('5555.00', $response['storeConfig']['check_money_order_max_order_total']);
        self::assertEquals(7, $response['storeConfig']['check_money_order_sort_order']);
    }
}
