<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Fixture;

use Mtf\Factory\Factory;

class VirtualProduct extends Product
{
    /**
     * {@inheritdoc}
     */
    protected function _initData()
    {
        parent::_initData();
        $this->_dataConfig = [
            'constraint' => 'Success',

            'grid_filter' => ['name'],

            'create_url_params' => [
                'type' => 'virtual',
                'set'  => static::DEFAULT_ATTRIBUTE_SET_ID,
            ],
            'input_prefix' => 'product',
        ];

        $data = [
            'is_virtual' => ['value' => '', 'group' => null], // needed for CURL handler
            'price' => [
                'value' => 15,
                'group' => static::GROUP_PRODUCT_DETAILS,
            ],
            'tax_class_id' => [
                'value' => 'Taxable Goods',
                'input_value' => '2',
                'group' => static::GROUP_PRODUCT_DETAILS,
                'input' => 'select',
            ],
            'qty' => [
                'value' => 1000,
                'group' => static::GROUP_PRODUCT_DETAILS,
                'input_name' => 'product[quantity_and_stock_status][qty]',
            ],
            'product_website_1' => [
                'value' => 'Yes',
                'input_value' => 1,
                'group' => static::GROUP_PRODUCT_WEBSITE,
                'input' => 'checkbox',
                'input_name' => 'product[website_ids][]',
            ],
            'inventory_manage_stock' => [
                'value' => 'No',
                'input_value' => '0',
                'group' => static::GROUP_PRODUCT_INVENTORY,
                'input' => 'select',
                'input_name' => 'product[stock_data][manage_stock]',
            ],
        ];

        $this->_data['fields'] = array_merge($this->_data['fields'], $data);

        $this->_repository = Factory::getRepositoryFactory()
            ->getMagentoCatalogVirtualProduct($this->_dataConfig, $this->_data);
    }
}
