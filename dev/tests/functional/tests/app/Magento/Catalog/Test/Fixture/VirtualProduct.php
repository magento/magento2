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
        $this->_dataConfig = array(
            'constraint' => 'Success',

            'grid_filter' => array('name'),

            'create_url_params' => array(
                'type' => 'virtual',
                'set'  => static::DEFAULT_ATTRIBUTE_SET_ID,
            ),
            'input_prefix' => 'product'
        );

        $data = array(
            'is_virtual' => array('value' => '', 'group' => null), // needed for CURL handler
            'price' => array(
                'value' => 15,
                'group' => static::GROUP_PRODUCT_DETAILS
            ),
            'tax_class_id' => array(
                'value' => 'Taxable Goods',
                'input_value' => '2',
                'group' => static::GROUP_PRODUCT_DETAILS,
                'input' => 'select'
            ),
            'qty' => array(
                'value' => 1000,
                'group' => static::GROUP_PRODUCT_DETAILS,
                'input_name' => 'product[quantity_and_stock_status][qty]'
            ),
            'product_website_1' => array(
                'value' => 'Yes',
                'input_value' => 1,
                'group' => static::GROUP_PRODUCT_WEBSITE,
                'input' => 'checkbox',
                'input_name' => 'product[website_ids][]'
            ),
            'inventory_manage_stock' => array(
                'value' => 'No',
                'input_value' => '0',
                'group' => static::GROUP_PRODUCT_INVENTORY,
                'input' => 'select',
                'input_name' => 'product[stock_data][manage_stock]'
            )
        );

        $this->_data['fields'] = array_merge($this->_data['fields'], $data);

        $this->_repository = Factory::getRepositoryFactory()
            ->getMagentoCatalogVirtualProduct($this->_dataConfig, $this->_data);
    }
}
