<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Repository;

use Mtf\Repository\AbstractRepository;

/**
 * Data for creation Catalog Product Simple.
 */
class CatalogProductSimple extends AbstractRepository
{
    /**
     * Constructor
     *
     * @param array $defaultConfig [optional]
     * @param array $defaultData [optional]
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function __construct(array $defaultConfig = [], array $defaultData = [])
    {
        $this->_data['default'] = [
            'attribute_set_id' => ['dataSet' => 'default'],
            'name' => 'Simple Product %isolation%',
            'sku' => 'sku_simple_product_%isolation%',
            'is_virtual' => 'No',
            'weight' => 1,
            'quantity_and_stock_status' => [
                'qty' => 25.0000,
                'is_in_stock' => 'In Stock',
            ],
            'price' => ['value' => 560.00, 'preset' => '-'],
            'tax_class_id' => ['dataSet' => 'Taxable Goods'],
            'website_ids' => ['Main Website'],
            'visibility' => 'Catalog, Search',
            'checkout_data' => ['preset' => 'order_default'],
        ];

        $this->_data['product_with_url_key'] = [
            'name' => 'Simple Product %isolation%',
            'sku' => 'sku_simple_product_%isolation%',
            'is_virtual' => 'No',
            'weight' => 1,
            'quantity_and_stock_status' => [
                'qty' => 25.0000,
                'is_in_stock' => 'In Stock',
            ],
            'url_key' => 'simple-product-%isolation%',
            'price' => ['value' => 560.00, 'preset' => '-'],
            'website_ids' => ['Main Website'],
            'visibility' => 'Catalog, Search',
            'checkout_data' => ['preset' => 'order_default'],
        ];

        $this->_data['simple_big_qty'] = [
            'attribute_set_id' => ['dataSet' => 'default'],
            'name' => 'Simple Product %isolation%',
            'sku' => 'sku_simple_product_%isolation%',
            'weight' => 1,
            'quantity_and_stock_status' => [
                'qty' => 1000.0000,
                'is_in_stock' => 'In Stock',
            ],
            'price' => ['value' => 560.00, 'preset' => '-'],
            'tax_class_id' => ['dataSet' => 'Taxable Goods'],
            'website_ids' => ['Main Website'],
            'visibility' => 'Catalog, Search',
            'checkout_data' => ['preset' => 'order_big_qty'],
        ];

        $this->_data['simple_for_sales'] = [
            'attribute_set_id' => ['dataSet' => 'default'],
            'name' => 'Simple Product %isolation%',
            'sku' => 'sku_simple_product_%isolation%',
            'weight' => 1,
            'quantity_and_stock_status' => [
                'qty' => 25.0000,
                'is_in_stock' => 'In Stock',
            ],
            'price' => ['value' => 560.00, 'preset' => '-'],
            'tax_class_id' => ['dataSet' => 'Taxable Goods'],
            'website_ids' => ['Main Website'],
            'visibility' => 'Catalog, Search',
            'checkout_data' => ['preset' => 'order_custom_price'],
        ];

        $this->_data['100_dollar_product'] = [
            'sku' => '100_dollar_product%isolation%',
            'name' => '100_dollar_product%isolation%',
            'type_id' => 'simple',
            'quantity_and_stock_status' => [
                'qty' => 666.0000,
                'is_in_stock' => 'In Stock',
            ],
            'weight' => 1,
            'attribute_set_id' => ['dataSet' => 'default'],
            'price' => ['value' => 100, 'preset' => '-'],
            'website_ids' => ['Main Website'],
            'checkout_data' => ['preset' => 'two_products']
        ];

        $this->_data['40_dollar_product'] = [
            'sku' => '40_dollar_product%isolation%',
            'name' => '40_dollar_product%isolation%',
            'type_id' => 'simple',
            'quantity_and_stock_status' => [
                'qty' => 666.0000,
                'is_in_stock' => 'In Stock',
            ],
            'weight' => 1,
            'attribute_set_id' => ['dataSet' => 'default'],
            'price' => ['value' => 40, 'preset' => '-'],
            'mtf_dataset_name' => '40_dollar_product',
            'website_ids' => ['Main Website'],
        ];

        $this->_data['MAGETWO-23036'] = [
            'sku' => 'MAGETWO-23036_%isolation%',
            'name' => 'simple_with_category %isolation%',
            'attribute_set_id' => ['dataSet' => 'default'],
            'type_id' => 'simple',
            'quantity_and_stock_status' => [
                'qty' => 666.0000,
                'is_in_stock' => 'In Stock',
            ],
            'weight' => 1,
            'price' => ['value' => 100, 'preset' => 'MAGETWO-23036'],
            'category_ids' => ['presets' => 'default_subcategory'],
            'mtf_dataset_name' => 'simple_with_category',
            'website_ids' => ['Main Website'],
        ];

        $this->_data['product_with_category'] = [
            'sku' => 'simple_product_with_category_%isolation%',
            'name' => 'Simple product with category %isolation%',
            'quantity_and_stock_status' => [
                'qty' => 666.0000,
                'is_in_stock' => 'In Stock',
            ],
            'weight' => 1,
            'attribute_set_id' => ['dataSet' => 'default'],
            'price' => ['value' => 100, 'preset' => ''],
            'category_ids' => ['presets' => 'default_subcategory'],
            'website_ids' => ['Main Website'],
            'mtf_dataset_name' => 'simple_with_category',
        ];

        $this->_data['simple_for_salesrule_1'] = [
            'attribute_set_id' => ['dataSet' => 'default'],
            'type_id' => 'simple',
            'quantity_and_stock_status' => [
                'qty' => 666.0000,
                'is_in_stock' => 'In Stock',
            ],
            'name' => 'Simple Product %isolation%',
            'sku' => 'sku_simple_product_%isolation%',
            'price' => ['value' => 100, 'preset' => ''],
            'weight' => 100,
            'website_ids' => ['Main Website'],
            'category_ids' => ['presets' => 'default_subcategory']
        ];

        $this->_data['simple_for_composite_products'] = [
            'name' => 'simple_for_composite_products%isolation%',
            'sku' => 'simple_for_composite_products%isolation%',
            'price' => ['value' => 560, 'preset' => '-'],
            'attribute_set_id' => ['dataSet' => 'default'],
            'tax_class_id' => ['dataSet' => 'Taxable Goods'],
            'quantity_and_stock_status' => [
                'qty' => 111,
                'is_in_stock' => 'In Stock',
            ],
            'weight' => '1',
            'status' => '1',
            'website_ids' => ['Main Website'],
            'stock_data' => [
                'manage_stock' => 'Yes',
                'qty' => '111',
                'is_in_stock' => 'In Stock'
            ],
            'url_key' => 'simple-for-composite-products%isolation%',
            'visibility' => 'Catalog, Search'
        ];

        $this->_data['simple_for_salesrule_2'] = [
            'attribute_set_id' => ['dataSet' => 'default'],
            'name' => 'Simple Product %isolation%',
            'sku' => 'sku_simple_product_%isolation%',
            'price' => ['value' => 50, 'preset' => ''],
            'weight' => 50,
            'website_ids' => ['Main Website'],
            'category_ids' => ['presets' => 'default_subcategory']
        ];

        $this->_data['product_with_special_price_and_category'] = [
            'sku' => 'simple_product_with_special_price_and_category%isolation%',
            'name' => 'Simple product with special price and category %isolation%',
            'attribute_set_id' => ['dataSet' => 'default'],
            'price' => ['value' => 100, 'preset' => ''],
            'special_price' => 90,
            'category_ids' => ['presets' => 'default_subcategory'],
            'website_ids' => ['Main Website'],
        ];

        $this->_data['product_with_special_price'] = [
            'sku' => 'simple_product_with_special_price_and_category%isolation%',
            'name' => 'Simple with Special Price 1$ off %isolation%',
            'attribute_set_id' => ['dataSet' => 'default'],
            'price' => ['value' => 10, 'preset' => ''],
            'special_price' => 9,
            'website_ids' => ['Main Website'],
        ];

        $this->_data['adc_123_simple_for_advancedsearch'] = [
            'name' => 'adc_123_%isolation%',
            'sku' => 'adc_123',
            'price' => ['value' => 100.00, 'preset' => '-'],
            'tax_class_id' => ['dataSet' => 'None'],
            'quantity_and_stock_status' => [
                'qty' => 666.0000,
                'is_in_stock' => 'In Stock',
            ],
            'weight' => 1.0000,
            'description' => '<p>dfj_full</p>',
            'status' => 'Product online',
            'website_ids' => ['Main Website'],
            'visibility' => 'Catalog, Search',
        ];

        $this->_data['product_with_weight_0_1'] = [
            'name' => 'Simple with Weight 0.1 %isolation%',
            'sku' => 'adc_123',
            'price' => ['value' => 100.00, 'preset' => '-'],
            'tax_class_id' => ['dataSet' => 'None'],
            'quantity_and_stock_status' => [
                'qty' => 666.0000,
                'is_in_stock' => 'In Stock',
            ],
            'weight' => 0.1,
            'description' => '<p>Simple with Weight 0.1</p>',
            'status' => 'Product online',
            'website_ids' => ['Main Website'],
            'visibility' => 'Catalog, Search',
        ];

        $this->_data['product_with_weight_150_1'] = [
            'name' => 'Simple with Weight 150.1 %isolation%',
            'sku' => 'adc_123',
            'price' => ['value' => 100.00, 'preset' => '-'],
            'tax_class_id' => ['dataSet' => 'None'],
            'quantity_and_stock_status' => [
                'qty' => 666.0000,
                'is_in_stock' => 'In Stock',
            ],
            'weight' => 150.1,
            'description' => '<p>Simple with Weight 150.1</p>',
            'status' => 'Product online',
            'website_ids' => ['Main Website'],
            'visibility' => 'Catalog, Search',
        ];

        $this->_data['abc_dfj_simple_for_advancedsearch'] = [
            'name' => 'abc_dfj_%isolation%',
            'sku' => 'abc_dfj',
            'price' => ['value' => 50.00, 'preset' => '-'],
            'tax_class_id' => ['dataSet' => 'Taxable Goods'],
            'quantity_and_stock_status' => [
                'qty' => 666.0000,
                'is_in_stock' => 'In Stock',
            ],
            'weight' => 1.0000,
            'description' => '<p>adc_Full</p>',
            'status' => 'Product online',
            'short_description' => '<p>abc_short</p>',
            'website_ids' => ['Main Website'],
            'visibility' => 'Catalog, Search',
        ];

        $this->_data['100_dollar_product_for_tax_rule'] = [
            'sku' => '100_dollar_product%isolation%',
            'name' => '100_dollar_product%isolation%',
            'attribute_set_id' => ['dataSet' => 'default'],
            'type_id' => 'simple',
            'quantity_and_stock_status' => [
                'qty' => 25.0000,
                'is_in_stock' => 'In Stock',
            ],
            'weight' => 1,
            'price' => ['value' => 100, 'preset' => '-'],
            'website_ids' => ['Main Website'],
        ];

        $this->_data['withSpecialPrice'] = [
            'type_id' => 'simple',
            'attribute_set_id' => ['dataSet' => 'default'],
            'name' => 'Simple Product %isolation%',
            'sku' => 'sku_simple_product_%isolation%',
            'price' => ['value' => 100, 'preset' => '-'],
            'weight' => 1,
            'special_price' => 9
        ];

        $this->_data['simple_with_group_price'] = [
            'type_id' => 'simple',
            'attribute_set_id' => ['dataSet' => 'default'],
            'name' => 'Simple Product %isolation%',
            'sku' => 'sku_simple_product_%isolation%',
            'price' => ['value' => 100, 'preset' => '-'],
            'weight' => 1,
            'group_price' => ['preset' => 'default'],
        ];

        $this->_data['simple_with_group_price_and_category'] = [
            'type_id' => 'simple',
            'attribute_set_id' => ['dataSet' => 'default'],
            'name' => 'Simple Product %isolation%',
            'sku' => 'sku_simple_product_%isolation%',
            'price' => ['value' => 100, 'preset' => '-'],
            'weight' => 1,
            'group_price' => ['preset' => 'tax_calculation'],
            'category_ids' => ['presets' => 'default_subcategory'],
            'website_ids' => ['Main Website'],
        ];

        $this->_data['simple_with_tier_price'] = [
            'type_id' => 'simple',
            'attribute_set_id' => ['dataSet' => 'default'],
            'name' => 'Simple Product %isolation%',
            'sku' => 'sku_simple_product_%isolation%',
            'price' => ['value' => 300, 'preset' => '-'],
            'weight' => 1,
            'tier_price' => ['preset' => 'default'],
        ];

        $this->_data['simple_with_tier_price_and_category'] = [
            'type_id' => 'simple',
            'attribute_set_id' => ['dataSet' => 'default'],
            'name' => 'Simple Product %isolation%',
            'sku' => 'sku_simple_product_%isolation%',
            'price' => ['value' => 300, 'preset' => '-'],
            'weight' => 1,
            'tier_price' => ['preset' => 'default'],
            'category_ids' => ['presets' => 'default_subcategory'],
            'website_ids' => ['Main Website'],
        ];

        $this->_data['with_two_custom_option'] = [
            'type_id' => 'simple',
            'attribute_set_id' => ['dataSet' => 'default'],
            'name' => 'Simple Product %isolation%',
            'sku' => 'sku_simple_product_%isolation%',
            'price' => ['value' => 300, 'preset' => '-'],
            'weight' => 1,
            'custom_options' => ['preset' => 'two_options'],
            'checkout_data' => ['preset' => 'with_two_custom_option']
        ];

        $this->_data['with_one_custom_option_and_category'] = [
            'type_id' => 'simple',
            'attribute_set_id' => ['dataSet' => 'default'],
            'name' => 'Simple Product %isolation%',
            'sku' => 'sku_simple_product_%isolation%',
            'price' => ['value' => 300, 'preset' => '-'],
            'weight' => 1,
            'custom_options' => ['preset' => 'drop_down_with_one_option_percent_price'],
            'checkout_data' => ['preset' => 'drop_down_with_one_option_percent_price'],
            'website_ids' => ['Main Website'],
            'category_ids' => ['presets' => 'default_subcategory'],
        ];

        $this->_data['with_all_custom_option'] = [
            'type_id' => 'simple',
            'attribute_set_id' => ['dataSet' => 'default'],
            'name' => 'Simple Product With Custom Option %isolation%',
            'sku' => 'sku_simple_product_%isolation%',
            'price' => ['value' => 300, 'preset' => '-'],
            'weight' => 1,
            'custom_options' => ['preset' => 'all_types'],
        ];

        $this->_data['low_stock_product'] = [
            'sku' => 'low_stock_product%isolation%',
            'name' => 'low_stock_product%isolation%',
            'type_id' => 'simple',
            'quantity_and_stock_status' => [
                'qty' => 1.0000,
                'is_in_stock' => 'In Stock',
            ],
            'stock_data' => [
                'use_config_notify_stock_qty' => 'No',
                'notify_stock_qty' => 2,
            ],
            'weight' => 1,
            'attribute_set_id' => ['dataSet' => 'default'],
            'price' => ['value' => 100, 'preset' => '-'],
            'website_ids' => ['Main Website'],
        ];

        $this->_data['out_of_stock'] = [
            'attribute_set_id' => ['dataSet' => 'default'],
            'name' => 'Simple Product out of stock %isolation%',
            'sku' => 'sku_simple_product_out_of_stock%isolation%',
            'weight' => 1,
            'quantity_and_stock_status' => [
                'qty' => 25.0000,
                'is_in_stock' => 'Out of Stock',
            ],
            'price' => ['value' => 560.00, 'preset' => '-'],
            'tax_class_id' => ['dataSet' => 'Taxable Goods'],
            'website_ids' => ['Main Website'],
            'visibility' => 'Catalog, Search',
            'checkout_data' => ['preset' => 'order_default'],
        ];

        $this->_data['offline'] = [
            'attribute_set_id' => ['dataSet' => 'default'],
            'name' => 'Simple Product offline %isolation%',
            'sku' => 'sku_simple_product_offline_%isolation%',
            'weight' => 1,
            'quantity_and_stock_status' => [
                'qty' => 25.0000,
                'is_in_stock' => 'In Stock',
            ],
            'price' => ['value' => 560.00, 'preset' => '-'],
            'tax_class_id' => ['dataSet' => 'Taxable Goods'],
            'website_ids' => ['Main Website'],
            'visibility' => 'Catalog, Search',
            'checkout_data' => ['preset' => 'order_default'],
            'status' => 'Product offline',
        ];

        $this->_data['not_visible_individually'] = [
            'attribute_set_id' => ['dataSet' => 'default'],
            'name' => 'Simple Product not visible %isolation%',
            'sku' => 'sku_simple_product_not_visible_%isolation%',
            'weight' => 1,
            'quantity_and_stock_status' => [
                'qty' => 25.0000,
                'is_in_stock' => 'In Stock',
            ],
            'price' => ['value' => 560.00, 'preset' => '-'],
            'tax_class_id' => ['dataSet' => 'Taxable Goods'],
            'website_ids' => ['Main Website'],
            'visibility' => 'Not Visible Individually',
            'checkout_data' => ['preset' => 'order_default'],
        ];

        $this->_data['simple_with_cart_limits'] = [
            'attribute_set_id' => ['dataSet' => 'default'],
            'name' => 'Simple Product with cart limit %isolation%',
            'sku' => 'sku_simple_product_with_cart_limit_%isolation%',
            'weight' => 1,
            'quantity_and_stock_status' => [
                'qty' => 25.0000,
                'is_in_stock' => 'In Stock',
            ],
            'price' => ['value' => 560.00, 'preset' => '-'],
            'tax_class_id' => ['dataSet' => 'Taxable Goods'],
            'website_ids' => ['Main Website'],
            'visibility' => 'Catalog, Search',
            'checkout_data' => ['preset' => 'order_default'],
            'stock_data' => [
                'min_sale_qty' => '2',
                'max_sale_qty' => '5',
            ],
        ];

        $this->_data['with_one_custom_option'] = [
            'type_id' => 'simple',
            'attribute_set_id' => ['dataSet' => 'default'],
            'name' => 'Simple Product %isolation%',
            'sku' => 'sku_simple_product_%isolation%',
            'price' => ['value' => 300, 'preset' => '-'],
            'weight' => 1,
            'custom_options' => ['preset' => 'drop_down_with_one_option_percent_price'],
            'checkout_data' => ['preset' => 'drop_down_with_one_option_percent_price'],
            'website_ids' => ['Main Website']
        ];

        $this->_data['simple_with_qty_increments'] = [
            'attribute_set_id' => ['dataSet' => 'default'],
            'name' => 'Simple Product with qty increments %isolation%',
            'sku' => 'sku_simple_product_with_qty_increments_%isolation%',
            'weight' => 1,
            'quantity_and_stock_status' => [
                'qty' => 25.0000,
                'is_in_stock' => 'In Stock',
            ],
            'price' => ['value' => 560.00, 'preset' => '-'],
            'tax_class_id' => ['dataSet' => 'Taxable Goods'],
            'website_ids' => ['Main Website'],
            'visibility' => 'Catalog, Search',
            'checkout_data' => ['preset' => 'order_default'],
            'stock_data' => [
                'enable_qty_increments' => 'Yes',
                'qty_increments' => '2',
            ],
        ];

        $this->_data['simple_with_tier_price_and_qty'] = [
            'type_id' => 'simple',
            'attribute_set_id' => ['dataSet' => 'default'],
            'name' => 'Simple Product %isolation%',
            'sku' => 'sku_simple_product_%isolation%',
            'price' => ['value' => 300, 'preset' => '-'],
            'weight' => 1,
            'quantity_and_stock_status' => [
                'qty' => 25.0000,
                'is_in_stock' => 'In Stock',
            ],
            'tier_price' => ['preset' => 'default'],
            'website_ids' => ['Main Website']
        ];

        $this->_data['with_msrp'] = [
            'attribute_set_id' => ['dataSet' => 'default'],
            'name' => 'Simple Product with msrp %isolation%',
            'sku' => 'sku_simple_product_with_msrp_%isolation%',
            'weight' => 1,
            'quantity_and_stock_status' => [
                'qty' => 25.0000,
                'is_in_stock' => 'In Stock',
            ],
            'price' => ['value' => 560.00, 'preset' => '-'],
            'tax_class_id' => ['dataSet' => 'Taxable Goods'],
            'website_ids' => ['Main Website'],
            'visibility' => 'Catalog, Search',
            'checkout_data' => ['preset' => 'order_default'],
            'msrp' => 600.00,
            'msrp_display_actual_price_type' => 'Before Order Confirmation'
        ];

        $this->_data['with_one_custom_option_and_category'] = [
            'type_id' => 'simple',
            'attribute_set_id' => ['dataSet' => 'default'],
            'name' => 'Simple Product %isolation%',
            'sku' => 'sku_simple_product_%isolation%',
            'price' => ['value' => 300, 'preset' => '-'],
            'weight' => 1,
            'custom_options' => ['preset' => 'drop_down_with_one_option_percent_price'],
            'checkout_data' => ['preset' => 'drop_down_with_one_option_percent_price'],
            'website_ids' => ['Main Website'],
            'category_ids' => ['presets' => 'default_subcategory'],
        ];

        $this->_data['product_with_category_with_anchor'] = [
            'sku' => 'simple_product_with_category_%isolation%',
            'name' => 'Simple product with category %isolation%',
            'quantity_and_stock_status' => [
                'qty' => 666.0000,
                'is_in_stock' => 'In Stock',
            ],
            'weight' => 1,
            'attribute_set_id' => ['dataSet' => 'default'],
            'price' => ['value' => 100, 'preset' => ''],
            'category_ids' => ['presets' => 'default_anchor_subcategory'],
            'website_ids' => ['Main Website'],
            'mtf_dataset_name' => 'simple_with_category',
        ];

        $this->_data['with_custom_option_and_fpt'] = [
            'type_id' => 'simple',
            'attribute_set_id' => ['dataSet' => 'default'],
            'category_ids' => ['presets' => 'default_subcategory'],
            'website_ids' => ['Main Website'],
            'name' => 'Simple Product With Fpt %isolation%',
            'sku' => 'sku_simple_product_%isolation%',
            'price' => ['value' => 70, 'preset' => '-'],
            'weight' => 1,
            'custom_options' => ['preset' => 'drop_down_with_one_option_fixed_price'],
            'checkout_data' => ['preset' => 'drop_down_with_one_option_fixed_price'],
            'fpt' => ['preset' => 'one_fpt_for_all_states']
        ];

        $this->_data['with_special_price_and_fpt'] = [
            'type_id' => 'simple',
            'attribute_set_id' => ['dataSet' => 'default'],
            'category_ids' => ['presets' => 'default_subcategory'],
            'website_ids' => ['Main Website'],
            'name' => 'Simple Product With Fpt %isolation%',
            'sku' => 'sku_simple_product_%isolation%',
            'price' => ['value' => 110, 'preset' => '-'],
            'special_price' => 100,
            'weight' => 1,
            'fpt' => ['preset' => 'one_fpt_for_all_states']
        ];
    }
}
