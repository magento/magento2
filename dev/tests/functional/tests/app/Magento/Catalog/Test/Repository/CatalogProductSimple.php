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
     * @param array $defaultConfig
     * @param array $defaultData
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function __construct(array $defaultConfig = [], array $defaultData = [])
    {
        $this->_data['default'] = [
            'type_id' => 'simple',
            'attribute_set_id' => 'Default',
            'name' => 'Simple Product %isolation%',
            'sku' => 'sku_simple_product_%isolation%',
            'weight' => 1,
            'quantity_and_stock_status' => 'In Stock',
            'qty' => 25,
            'price' => ['value' => 560, 'preset' => '-'],
            'website_ids' => ['Main Website'],
        ];

        $this->_data['100_dollar_product'] = [
            'sku' => '100_dollar_product%isolation%',
            'name' => '100_dollar_product%isolation%',
            'type_id' => 'simple',
            'attribute_set_id' => 'Default',
            'quantity_and_stock_status' => 'In Stock',
            'price' => ['value' => 100, 'preset' => '-'],
            'website_ids' => ['Main Website'],
        ];

        $this->_data['40_dollar_product'] = [
            'sku' => '40_dollar_product',
            'name' => '40_dollar_product',
            'type_id' => 'simple',
            'attribute_set_id' => 'Default',
            'quantity_and_stock_status' => 'In Stock',
            'price' => ['value' => 40, 'preset' => '-'],
            'id' => '2',
            'mtf_dataset_name' => '40_dollar_product',
            'website_ids' => ['Main Website'],
        ];

        $this->_data['MAGETWO-23036'] = [
            'sku' => 'MAGETWO-23036',
            'name' => 'simple_with_category',
            'type_id' => 'simple',
            'attribute_set_id' => 'Default',
            'quantity_and_stock_status' => 'In Stock',
            'price' => ['value' => 100, 'preset' => 'MAGETWO-23036'],
            'id' => '3',
            'category_ids' => ['presets' => 'default'],
            'mtf_dataset_name' => 'simple_with_category',
            'website_ids' => ['Main Website'],
        ];

        $this->_data['product_with_category'] = [
            'sku' => 'simple_product_with_category_%isolation%',
            'name' => 'Simple product with category %isolation%',
            'type_id' => 'simple',
            'attribute_set_id' => 'Default',
            'price' => ['value' => 100, 'preset' => ''],
            'category_ids' => ['presets' => 'default_subcategory'],
            'website_ids' => ['Main Website'],
            'mtf_dataset_name' => 'simple_with_category',
        ];

        $this->_data['simple_for_salesrule_1'] = [
            'type_id' => 'simple',
            'attribute_set_id' => 'Default',
            'quantity_and_stock_status' => 'In Stock',
            'name' => 'Simple Product %isolation%',
            'sku' => 'sku_simple_product_%isolation%',
            'price' => ['value' => 100, 'preset' => ''],
            'weight' => 100,
            'website_ids' => ['Main Website'],
            'category_ids' => ['presets' => 'default_subcategory']
        ];

        $this->_data['simple_for_salesrule_2'] = [
            'type_id' => 'simple',
            'attribute_set_id' => 'Default',
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
            'type_id' => 'simple',
            'attribute_set_id' => 'Default',
            'price' => ['value' => 100, 'preset' => ''],
            'special_price' => 90,
            'category_ids' => ['presets' => 'default_subcategory'],
            'website_ids' => ['Main Website'],
        ];

        $this->_data['adc_123_simple_for_advancedsearch'] = [
            'name' => 'adc_123',
            'sku' => 'adc_123',
            'price' => ['value' => 100.00, 'preset' => '-'],
            'tax_class_id' => ['dataSet' => 'None'],
            'quantity_and_stock_status' => [
                'qty' => 666,
                'is_in_stock' => 'Yes',
            ],
            'weight' => 1.0000,
            'description' => '<p>dfj_full</p>',
            'status' => 'Product online',
            'website_ids' => [
                0 => 'Main Website',
            ],
            'stock_data' => [
                'manage_stock' => 'Yes',
                'original_inventory_qty' => 666,
                'qty' => 666.0000,
                'is_in_stock' => 'In Stock',
            ],
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
            'website_ids' => [
                0 => 'Main Website',
            ],
            'stock_data' => [
                'manage_stock' => 'Yes',
                'original_inventory_qty' => 666,
                'qty' => 666.0000,
                'is_in_stock' => 'Yes',
            ],
            'visibility' => 'Catalog, Search',
        ];

        $this->_data['100_dollar_product_for_tax_rule'] = [
            'sku' => '100_dollar_product%isolation%',
            'name' => '100_dollar_product%isolation%',
            'type_id' => 'simple',
            'attribute_set_id' => 'Default',
            'quantity_and_stock_status' => 'In Stock',
            'qty' => 25,
            'price' => ['value' => 100, 'preset' => '-'],
            'website_ids' => ['Main Website'],
        ];
    }
}
