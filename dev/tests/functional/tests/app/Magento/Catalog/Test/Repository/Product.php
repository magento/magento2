<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
    public function __construct(array $defaultConfig = [], array $defaultData = [])
    {
        $this->_data['default'] = [
            'config' => $defaultConfig,
            'data' => $defaultData,
        ];
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
        return [
            'data' => [
                'fields' => [
                    'price' => ['value' => '1.99', 'group' => 'attributes_update_tabs_attributes'],
                ],
            ]
        ];
    }

    /**
     * @param string $productType
     * @return array
     */
    protected function resetRequiredFields($productType)
    {
        return [
            'data' => [
                'fields' => [
                    'name' => [
                        'value' => 'edited ' . $productType . ' %isolation%',
                        'group' => Fixture\Product::GROUP_PRODUCT_DETAILS,
                    ],
                    'sku' => [
                        'value' => 'edited ' . $productType . '_sku_%isolation%',
                        'group' => Fixture\Product::GROUP_PRODUCT_DETAILS,
                    ],
                ],
            ]
        ];
    }

    /**
     * Get simple product with advanced inventory
     *
     * @return array
     */
    protected function _getSimpleOutOfStock()
    {
        $inventory = [
            'data' => [
                'fields' => [
                    'inventory_manage_stock' => [
                        'value' => 'Yes',
                        'input_value' => 1,
                    ],
                    'inventory_qty' => [
                        'value' => 0,
                        'group' => Fixture\Product::GROUP_PRODUCT_INVENTORY,
                    ],
                    'quantity_and_stock_status' => [
                        'value' => 'Out of Stock',
                        'input_value' => 0,
                        'group' => Fixture\Product::GROUP_PRODUCT_INVENTORY,
                        'input_name' => 'product[quantity_and_stock_status][is_in_stock]',
                    ],
                    'inventory_stock_availability' => [
                        'value' => 'Out of Stock', // Out of Stock
                        'input_value' => 0,
                        'group' => Fixture\Product::GROUP_PRODUCT_INVENTORY,
                        'input' => 'select',
                        'input_name' => 'product[stock_data][is_in_stock]',
                    ],
                ],
            ],
        ];
        $product = array_replace_recursive($this->_data['simple'], $inventory);

        return $product;
    }
}
