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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
return array(
    'type_one' => array(
        'name' => 'type_one',
        'label' => 'Label One',
        'model' => 'Instance_Type',
        'composite' => true,
        'index_priority' => 40,
        'can_use_qty_decimals' => true,
        'is_qty' => true,
        'price_model' => 'Instance_Type_One',
        'price_indexer' => 'Instance_Type_Two',
        'stock_indexer' => 'Instance_Type_Three',
        'allow_product_types' => array(
            'type_one' => 'type_one'
        ),
    ),
    'type_two' => array(
        'name' => 'type_two',
        'label' => false,
        'model' => 'Instance_Type',
        'composite' => false,
        'index_priority' => 0,
        'can_use_qty_decimals' => false,
        'is_qty' => false,
        'allowed_selection_types' => array(
            'type_two' => 'type_two'
        )
    )
);
