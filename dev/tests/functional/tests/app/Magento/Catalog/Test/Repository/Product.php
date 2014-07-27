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

use Magento\Catalog\Test\Fixture;
use Mtf\Repository\AbstractRepository;

/**
 * Class Product Repository
 *
 */
class Product extends AbstractRepository
{
    /**
     * {@inheritdoc}
     */
    public function __construct(array $defaultConfig = array(), array $defaultData = array())
    {
        $this->_data['default'] = array(
            'config' => $defaultConfig,
            'data' => $defaultData
        );
        $type = str_replace('product', '', strtolower(substr(get_class($this), strrpos(get_class($this), '\\') + 1)));
        $this->_data[$type . '_required'] = $this->_data['default'];
        $this->_data[$type] = $this->_data['default'];
        $this->_data[$type]['data']['category_name'] = '%category::getCategoryName%';
        $this->_data[$type]['data']['category_id'] = '%category::getCategoryId%';
        $this->_data[$type . '_edit_required_fields'] = $this->resetRequiredFields($type);
        $this->_data['price_massaction'] = $this->_getMassActionPrice();
    }

    /**
     * Retrieve product price for mass action
     *
     * @return array
     */
    protected function _getMassActionPrice()
    {
        return array(
            'data' => array(
                'fields' => array(
                    'price' => array('value' => '1.99', 'group' => 'attributes_update_tabs_attributes')
                )
            )
        );
    }

    /**
     * @param string $productType
     * @return array
     */
    protected function resetRequiredFields($productType)
    {
        return array(
            'data' => array(
                'fields' => array(
                    'name' => array(
                        'value' => 'edited ' . $productType . ' %isolation%',
                        'group' => Fixture\Product::GROUP_PRODUCT_DETAILS
                    ),
                    'sku' => array(
                        'value' => 'edited ' . $productType . '_sku_%isolation%',
                        'group' => Fixture\Product::GROUP_PRODUCT_DETAILS
                    )
                )
            )
        );
    }

    /**
     * Get simple product with advanced inventory
     *
     * @return array
     */
    protected function _getSimpleOutOfStock()
    {
        $inventory = array(
            'data' => array(
                'fields' => array(
                    'inventory_manage_stock' => array(
                        'value' => 'Yes',
                        'input_value' => 1,
                    ),
                    'inventory_qty' => array(
                        'value' => 0,
                        'group' => Fixture\Product::GROUP_PRODUCT_INVENTORY
                    ),
                    'quantity_and_stock_status' => array(
                        'value' => 'Out of Stock',
                        'input_value' => 0,
                        'group' => Fixture\Product::GROUP_PRODUCT_INVENTORY,
                        'input_name' => 'product[quantity_and_stock_status][is_in_stock]'
                    ),
                    'inventory_stock_availability' => array(
                        'value' => 'Out of Stock', // Out of Stock
                        'input_value' => 0,
                        'group' => Fixture\Product::GROUP_PRODUCT_INVENTORY,
                        'input' => 'select',
                        'input_name' => 'product[stock_data][is_in_stock]'
                    )
                )
            )
        );
        $product = array_replace_recursive($this->_data['simple'], $inventory);

        return $product;
    }
}
