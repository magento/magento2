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

namespace Magento\Bundle\Test\Fixture;

use Mtf\Factory\Factory;

/**
 * Class BundleDynamic
 * Fixture for Bundle dynamic
 */
class BundleDynamic extends Bundle
{
    /**
     * Initialize fixture data
     */
    protected function _initData()
    {
        $this->_data['checkout'] = [
            'prices' => [
                'price_from' => 10,
                'price_to' => 15
            ],
            'selection' => [0]
        ];
        parent::_initData();
        $this->_data['fields'] = array_merge_recursive(
            $this->_data['fields'],
            [
                'sku_type' => [
                    'value' => 'Dynamic',
                    'input_value' => '0',
                    'group' => static::GROUP_PRODUCT_DETAILS,
                    'input' => 'select'
                ],
                'price_type' => [
                    'value' => 'Dynamic',
                    'input_value' => '0',
                    'group' => static::GROUP_PRODUCT_DETAILS,
                    'input' => 'select'
                ],
                'weight_type' => [
                    'value' => 'Dynamic',
                    'input_value' => '0',
                    'group' => static::GROUP_PRODUCT_DETAILS,
                    'input' => 'select'
                ],
                'product_website_1' => [
                    'value' => 'Yes',
                    'input_value' => [1],
                    'group' => static::GROUP_PRODUCT_WEBSITE,
                    'input' => 'checkbox',
                    'input_name' => 'website_ids'
                ],
                'shipment_type' => [
                    'value' => 'Separately',
                    'input_value' => '1',
                    'group' => static::GROUP_PRODUCT_DETAILS,
                    'input' => 'select'
                ],
                'bundle_selections' => [
                    'value' => [
                        'bundle_options' => [
                            [
                                'title' => 'Drop-down Option',
                                'type' => 'Drop-down',
                                'required' => 'Yes',
                                'assigned_products' => [
                                    [
                                        'search_data' => [
                                            'name' => '%item1_simple1::getName%',
                                        ],
                                        'data' => [
                                            'selection_qty' => 1,
                                            'product_id' => '%item1_simple1::getProductId%'
                                        ]
                                    ],
                                    [
                                        'search_data' => [
                                            'name' => '%item1_virtual2::getName%',
                                        ],
                                        'data' => [
                                            'selection_qty' => 1,
                                            'product_id' => '%item1_virtual2::getProductId%'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'group' => static::GROUP
                ]
            ]
        );
        $this->_repository = Factory::getRepositoryFactory()
            ->getMagentoBundleBundle($this->_dataConfig, $this->_data);
    }
}
