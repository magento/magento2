<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Sales;

use Exception;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class SalesTaxStoreConfigTest extends GraphQlAbstract
{
    private const CONFIG_KEYS = [
        'display_product_prices_in_catalog' => 1,
        'display_shipping_prices' => 2,
        'orders_invoices_credit_memos_display_price' => 1,
        'orders_invoices_credit_memos_display_subtotal' => 2,
        'orders_invoices_credit_memos_display_shipping_amount' => 3,
        'orders_invoices_credit_memos_display_grandtotal' => 0,
        'orders_invoices_credit_memos_display_full_summary' => 0,
        'orders_invoices_credit_memos_display_zero_tax' => 0,
        'fixed_product_taxes_enable' => 0,
        'fixed_product_taxes_display_prices_in_product_lists' => 1,
        'fixed_product_taxes_display_prices_on_product_view_page' => 1,
        'fixed_product_taxes_display_prices_in_sales_modules' => 1,
        'fixed_product_taxes_display_prices_in_emails' => 1,
        'fixed_product_taxes_apply_tax_to_fpt' => 0,
        'fixed_product_taxes_include_fpt_in_subtotal' => 0
    ];

    /**
     * @throws Exception
     */
    #[
        Config('tax/display/type', 1),
        Config('tax/display/shipping', 2),
        Config('tax/sales_display/price', 1),
        Config('tax/sales_display/subtotal', 2),
        Config('tax/sales_display/shipping', 3),
        Config('tax/sales_display/grandtotal', 0),
        Config('tax/sales_display/full_summary', 0),
        Config('tax/sales_display/zero_tax', 0),
        Config('tax/weee/enable', 0),
        Config('tax/weee/display_list', 1),
        Config('tax/weee/display', 1),
        Config('tax/weee/display_sales', 1),
        Config('tax/weee/display_email', 1),
        Config('tax/weee/apply_vat', 0),
        Config('tax/weee/include_in_subtotal', 0)
    ]
    public function testSalesTaxStoreConfig()
    {
        $response = $this->graphQlQuery($this->getQuery());
        $this->assertEquals(self::CONFIG_KEYS, $response['storeConfig']);
    }

    /**
     * Generates storeConfig query with newly added configurations from sales->tax
     *
     * @return string
     */
    private function getQuery(): string
    {
        return <<<QUERY
        {
            storeConfig {
                display_product_prices_in_catalog
                display_shipping_prices
                orders_invoices_credit_memos_display_price
                orders_invoices_credit_memos_display_subtotal
                orders_invoices_credit_memos_display_shipping_amount
                orders_invoices_credit_memos_display_grandtotal
                orders_invoices_credit_memos_display_full_summary
                orders_invoices_credit_memos_display_zero_tax
                fixed_product_taxes_enable
                fixed_product_taxes_display_prices_in_product_lists
                fixed_product_taxes_display_prices_on_product_view_page
                fixed_product_taxes_display_prices_in_sales_modules
                fixed_product_taxes_display_prices_in_emails
                fixed_product_taxes_apply_tax_to_fpt
                fixed_product_taxes_include_fpt_in_subtotal
          }
        }
        QUERY;
    }
}
