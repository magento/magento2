<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Sales;

use Exception;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class SalesTaxStoreConfigTest extends GraphQlAbstract
{
    private const CONFIG_KEYS = [
        'display_product_prices_in_catalog',
        'display_shipping_prices',
        'orders_invoices_credit_memos_display_price',
        'orders_invoices_credit_memos_display_subtotal',
        'orders_invoices_credit_memos_display_shipping_amount',
        'orders_invoices_credit_memos_display_grandtotal',
        'orders_invoices_credit_memos_display_full_summary',
        'orders_invoices_credit_memos_display_zero_tax',
        'fixed_product_taxes_enable',
        'fixed_product_taxes_display_prices_in_product_lists',
        'fixed_product_taxes_display_prices_on_product_view_page',
        'fixed_product_taxes_display_prices_in_sales_modules',
        'fixed_product_taxes_display_prices_in_emails',
        'fixed_product_taxes_apply_tax_to_fpt',
        'fixed_product_taxes_include_fpt_in_subtotal',
    ];
    /**
     * @throws Exception
     */
    public function testSalesTaxStoreConfigExists()
    {
        $response = $this->graphQlQuery($this->getQuery());
        $this->assertArrayHasKey('storeConfig', $response);
        $this->assertStoreConfigsExist($response['storeConfig']);
    }

    /**
     * Check if all the added store configs are returned in graphql response
     *
     * @param array $response
     * @return void
     */
    private function assertStoreConfigsExist(array $response): void
    {
        foreach (self::CONFIG_KEYS as $key) {
            $this->assertArrayHasKey($key, $response);
        }
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
