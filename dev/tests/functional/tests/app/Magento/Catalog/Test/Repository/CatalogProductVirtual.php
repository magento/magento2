<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Repository;

use Mtf\Repository\AbstractRepository;

/**
 * Class CatalogProductVirtual
 * Data for creation Catalog Product Virtual
 */
class CatalogProductVirtual extends AbstractRepository
{
    /**
     * Constructor
     *
     * @param array $defaultConfig [optional]
     * @param array $defaultData [optional]
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(array $defaultConfig = [], array $defaultData = [])
    {
        $this->_data['default'] = [
            'tax_class_id' => ['dataSet' => 'Taxable Goods'],
            'status' => 'Product online',
            'website_ids' => ['Main Website'],
            'is_virtual' => 'Yes',
            'url_key' => 'virtual-product%isolation%',
            'visibility' => 'Catalog, Search',
            'attribute_set_id' => ['dataSet' => 'default'],
            'name' => 'Virtual product %isolation%',
            'sku' => 'sku_virtual_product_%isolation%',
            'quantity_and_stock_status' => [
                'qty' => 666.0000,
                'is_in_stock' => 'In Stock',
            ],
            'price' => ['value' => 10.00, 'preset' => '-'],
            'checkout_data' => ['preset' => 'order_default'],
        ];

        $this->_data['virtual_big_qty'] = [
            'tax_class_id' => ['dataSet' => 'Taxable Goods'],
            'status' => 'Product online',
            'website_ids' => ['Main Website'],
            'is_virtual' => 'Yes',
            'url_key' => 'virtual-product%isolation%',
            'visibility' => 'Catalog, Search',
            'attribute_set_id' => ['dataSet' => 'default'],
            'name' => 'Virtual product %isolation%',
            'sku' => 'sku_virtual_product_%isolation%',
            'quantity_and_stock_status' => [
                'qty' => 1000.0000,
                'is_in_stock' => 'In Stock',
            ],
            'price' => ['value' => 10.00, 'preset' => '-'],
            'checkout_data' => ['preset' => 'order_big_qty'],
        ];

        $this->_data['virtual_for_sales'] = [
            'tax_class_id' => ['dataSet' => 'Taxable Goods'],
            'status' => 'Product online',
            'website_ids' => ['Main Website'],
            'is_virtual' => 'Yes',
            'url_key' => 'virtual-product%isolation%',
            'visibility' => 'Catalog, Search',
            'attribute_set_id' => ['dataSet' => 'default'],
            'name' => 'Virtual product %isolation%',
            'sku' => 'sku_virtual_product_%isolation%',
            'quantity_and_stock_status' => [
                'qty' => 666.0000,
                'is_in_stock' => 'In Stock',
            ],
            'price' => ['value' => 10.00, 'preset' => '-'],
            'checkout_data' => ['preset' => 'order_custom_price'],
        ];

        $this->_data['50_dollar_product'] = [
            'name' => '50_dollar_product %isolation%',
            'sku' => '50_dollar_product_%isolation%',
            'tax_class_id' => ['dataSet' => 'Taxable Goods'],
            'status' => 'Product online',
            'website_ids' => ['Main Website'],
            'is_virtual' => 'Yes',
            'url_key' => 'virtual-product%isolation%',
            'visibility' => 'Catalog, Search',
            'attribute_set_id' => ['dataSet' => 'default'],
            'quantity_and_stock_status' => [
                'qty' => 111.0000,
                'is_in_stock' => 'In Stock',
            ],
            'price' => ['value' => 50.00, 'preset' => '-'],
            'checkout_data' => ['preset' => '50_dollar_product'],
        ];
    }
}
