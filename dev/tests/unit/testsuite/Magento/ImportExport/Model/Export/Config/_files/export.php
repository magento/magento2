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
return array(
    'entities' => array(
        'product' => array(
            'name' => 'product',
            'label' => 'Label_One',
            'model' => 'Model_One',
            'types' => array(
                'product_type_one' => array('name' => 'product_type_one', 'model' => 'Product_Model_Type_One'),
                'type_two' => array('name' => 'type_two', 'model' => 'Model_Type_Two')
            ),
            'entityAttributeFilterType' => 'product'
        ),
        'customer' => array(
            'name' => 'customer',
            'label' => 'Label_One',
            'model' => 'Model_One',
            'types' => array(
                'type_one' => array('name' => 'type_one', 'model' => 'Model_Type_One'),
                'type_two' => array('name' => 'type_two', 'model' => 'Model_Type_Two')
            ),
            'entityAttributeFilterType' => 'customer'
        )
    ),
    'fileFormats' => array(
        'name_three' => array('name' => 'name_three', 'model' => 'Model_Three', 'label' => 'Label_Three')
    )
);
