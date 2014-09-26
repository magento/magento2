<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Test\Repository;

use Mtf\Repository\AbstractRepository;

/**
 * Class CatalogProductSimple
 * Data for creation Catalog Product Simple
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
            'sku' => 'MAGETWO-23036',
            'name' => 'simple_with_category',
            'attribute_set_id' => ['dataSet' => 'default'],
            'type_id' => 'simple',
            'quantity_and_stock_status' => [
                'qty' => 666.0000,
                'is_in_stock' => 'In Stock',
            ],
            'weight' => 1,
            'price' => ['value' => 100, 'preset' => 'MAGETWO-23036'],
            'category_ids' => ['presets' => 'default'],
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
            'name' => 'adc_123',
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
            'name' => 'abc_dfj',
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

        $this->_data['simple_with_tier_price'] = [
            'type_id' => 'simple',
            'attribute_set_id' => ['dataSet' => 'default'],
            'name' => 'Simple Product %isolation%',
            'sku' => 'sku_simple_product_%isolation%',
            'price' => ['value' => 300, 'preset' => '-'],
            'weight' => 1,
            'tier_price' => ['preset' => 'default'],
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
    }
}
