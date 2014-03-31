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
 * @category    Magento
 * @package     Magento_Catalog
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

return array(
    //empty attribute case
    array(
        false,
        false, //Attribute, isAddFilterable
        //Original attribute data
        array(
            array('backend_type', 'not_static'),
            array('is_filterable', 0),
            array('used_in_product_listing', 0),
            array('is_used_for_promo_rules', 0),
            array('used_for_sort_by', 0)
        ),
        //Attribute data
        array(
            array('backend_type', null, 'not_static'),
            array('is_filterable', null, 0),
            array('used_in_product_listing', null, 0),
            array('is_used_for_promo_rules', null, 0),
            array('used_for_sort_by', null, 0)
        ),
        \Magento\Index\Model\Event::TYPE_DELETE,
        false
    ), //Event Type, result
    //attribute exists, but shouldn't be matched
    array(
        true,
        false, //Attribute, isAddFilterable
        //Original attribute data
        array(
            array('backend_type', 'not_static'),
            array('is_filterable', 0),
            array('used_in_product_listing', 0),
            array('is_used_for_promo_rules', 0),
            array('used_for_sort_by', 0)
        ),
        //Attribute data
        array(
            array('backend_type', null, 'not_static'),
            array('is_filterable', null, 0),
            array('used_in_product_listing', null, 0),
            array('is_used_for_promo_rules', null, 0),
            array('used_for_sort_by', null, 0)
        ),
        \Magento\Index\Model\Event::TYPE_DELETE,
        false
    ), //Event Type, result
    //Next cases describe situation that one valuable argument exists
    array(
        true,
        false, //Attribute, isAddFilterable
        //Original attribute data
        array(
            array('backend_type', 'not_static'),
            array('is_filterable', 1),
            array('used_in_product_listing', 0),
            array('is_used_for_promo_rules', 0),
            array('used_for_sort_by', 0)
        ),
        //Attribute data
        array(
            array('backend_type', null, 'not_static'),
            array('is_filterable', null, 1),
            array('used_in_product_listing', null, 0),
            array('is_used_for_promo_rules', null, 0),
            array('used_for_sort_by', null, 0)
        ),
        \Magento\Index\Model\Event::TYPE_DELETE,
        false
    ),//Event Type, result
    array(
        true,
        true, //Attribute, isAddFilterable
        //Original attribute data
        array(
            array('backend_type', 'not_static'),
            array('is_filterable', 1),
            array('used_in_product_listing', 0),
            array('is_used_for_promo_rules', 0),
            array('used_for_sort_by', 0)
        ),
        //Attribute data
        array(
            array('backend_type', null, 'not_static'),
            array('is_filterable', null, 1),
            array('used_in_product_listing', null, 0),
            array('is_used_for_promo_rules', null, 0),
            array('used_for_sort_by', null, 0)
        ),
        \Magento\Index\Model\Event::TYPE_DELETE,
        true
    ), //Event Type, result
    array(
        true,
        false, //Attribute, isAddFilterable
        //Original attribute data
        array(
            array('backend_type', 'static'),
            array('is_filterable', 0),
            array('used_in_product_listing', 0),
            array('is_used_for_promo_rules', 0),
            array('used_for_sort_by', 0)
        ),
        //Attribute data
        array(
            array('backend_type', null, 'not_static'),
            array('is_filterable', null, 0),
            array('used_in_product_listing', null, 0),
            array('is_used_for_promo_rules', null, 0),
            array('used_for_sort_by', null, 0)
        ),
        \Magento\Index\Model\Event::TYPE_DELETE,
        true
    ), //Event Type, result
    array(
        true,
        false, //Attribute, isAddFilterable
        //Original attribute data
        array(
            array('backend_type', 'not_static'),
            array('is_filterable', 0),
            array('used_in_product_listing', 1),
            array('is_used_for_promo_rules', 0),
            array('used_for_sort_by', 0)
        ),
        //Attribute data
        array(
            array('backend_type', null, 'not_static'),
            array('is_filterable', null, 0),
            array('used_in_product_listing', null, 0),
            array('is_used_for_promo_rules', null, 0),
            array('used_for_sort_by', null, 0)
        ),
        \Magento\Index\Model\Event::TYPE_DELETE,
        true
    ), //Event Type, result
    array(
        true,
        false, //Attribute, isAddFilterable
        //Original attribute data
        array(
            array('backend_type', 'not_static'),
            array('is_filterable', 0),
            array('used_in_product_listing', 0),
            array('is_used_for_promo_rules', 1),
            array('used_for_sort_by', 0)
        ),
        //Attribute data
        array(
            array('backend_type', null, 'not_static'),
            array('is_filterable', null, 0),
            array('used_in_product_listing', null, 0),
            array('is_used_for_promo_rules', null, 0),
            array('used_for_sort_by', null, 0)
        ),
        \Magento\Index\Model\Event::TYPE_DELETE,
        true
    ), //Event Type, result
    array(
        true,
        false, //Attribute, isAddFilterable
        //Original attribute data
        array(
            array('backend_type', 'not_static'),
            array('is_filterable', 0),
            array('used_in_product_listing', 0),
            array('is_used_for_promo_rules', 0),
            array('used_for_sort_by', 1)
        ),
        //Attribute data
        array(
            array('backend_type', null, 'not_static'),
            array('is_filterable', null, 0),
            array('used_in_product_listing', null, 0),
            array('is_used_for_promo_rules', null, 0),
            array('used_for_sort_by', null, 0)
        ),
        \Magento\Index\Model\Event::TYPE_DELETE,
        true
    ), //Event Type, result
    //\Magento\Index\Model\Event::TYPE_SAVE cases
    array(
        true,
        false, //Attribute, isAddFilterable
        //Original attribute data
        array(
            array('backend_type', 'not_static'),
            array('is_filterable', 0),
            array('used_in_product_listing', 0),
            array('is_used_for_promo_rules', 0),
            array('used_for_sort_by', 0)
        ),
        //Attribute data
        array(
            array('backend_type', null, 'not_static'),
            array('is_filterable', null, 0),
            array('used_in_product_listing', null, 0),
            array('is_used_for_promo_rules', null, 0),
            array('used_for_sort_by', null, 0)
        ),
        \Magento\Index\Model\Event::TYPE_SAVE,
        false
    ), //Event Type, result
    array(
        true,
        false, //Attribute, isAddFilterable
        //Original attribute data
        array(
            array('backend_type', 'static'),
            array('is_filterable', 0),
            array('used_in_product_listing', 0),
            array('is_used_for_promo_rules', 0),
            array('used_for_sort_by', 0)
        ),
        //Attribute data
        array(
            array('backend_type', null, 'not_static'),
            array('is_filterable', null, 0),
            array('used_in_product_listing', null, 0),
            array('is_used_for_promo_rules', null, 0),
            array('used_for_sort_by', null, 0)
        ),
        \Magento\Index\Model\Event::TYPE_SAVE,
        true
    ), //Event Type, result
    array(
        true,
        false, //Attribute, isAddFilterable
        //Original attribute data
        array(
            array('backend_type', 'not_static'),
            array('is_filterable', 0),
            array('used_in_product_listing', 0),
            array('is_used_for_promo_rules', 0),
            array('used_for_sort_by', 0)
        ),
        //Attribute data
        array(
            array('backend_type', null, 'static'),
            array('is_filterable', null, 0),
            array('used_in_product_listing', null, 0),
            array('is_used_for_promo_rules', null, 0),
            array('used_for_sort_by', null, 0)
        ),
        \Magento\Index\Model\Event::TYPE_SAVE,
        true
    ), //Event Type, result
    array(
        true,
        true, //Attribute, isAddFilterable
        //Original attribute data
        array(
            array('backend_type', 'not_static'),
            array('is_filterable', 0),
            array('used_in_product_listing', 0),
            array('is_used_for_promo_rules', 0),
            array('used_for_sort_by', 0)
        ),
        //Attribute data
        array(
            array('backend_type', null, 'not_static'),
            array('is_filterable', null, 0),
            array('used_in_product_listing', null, 0),
            array('is_used_for_promo_rules', null, 0),
            array('used_for_sort_by', null, 0)
        ),
        \Magento\Index\Model\Event::TYPE_SAVE,
        false
    ), //Event Type, result
    array(
        true,
        true, //Attribute, isAddFilterable
        //Original attribute data
        array(
            array('backend_type', 'not_static'),
            array('is_filterable', 1),
            array('used_in_product_listing', 0),
            array('is_used_for_promo_rules', 0),
            array('used_for_sort_by', 0)
        ),
        //Attribute data
        array(
            array('backend_type', null, 'not_static'),
            array('is_filterable', null, 0),
            array('used_in_product_listing', null, 0),
            array('is_used_for_promo_rules', null, 0),
            array('used_for_sort_by', null, 0)
        ),
        \Magento\Index\Model\Event::TYPE_SAVE,
        true
    ), //Event Type, result
    array(
        true,
        true, //Attribute, isAddFilterable
        //Original attribute data
        array(
            array('backend_type', 'not_static'),
            array('is_filterable', 0),
            array('used_in_product_listing', 0),
            array('is_used_for_promo_rules', 0),
            array('used_for_sort_by', 0)
        ),
        //Attribute data
        array(
            array('backend_type', null, 'not_static'),
            array('is_filterable', null, 1),
            array('used_in_product_listing', null, 0),
            array('is_used_for_promo_rules', null, 0),
            array('used_for_sort_by', null, 0)
        ),
        \Magento\Index\Model\Event::TYPE_SAVE,
        true
    ), //Event Type, result
    array(
        true,
        false, //Attribute, isAddFilterable
        //Original attribute data
        array(
            array('backend_type', 'not_static'),
            array('is_filterable', 0),
            array('used_in_product_listing', 1),
            array('is_used_for_promo_rules', 0),
            array('used_for_sort_by', 0)
        ),
        //Attribute data
        array(
            array('backend_type', null, 'not_static'),
            array('is_filterable', null, 0),
            array('used_in_product_listing', null, 0),
            array('is_used_for_promo_rules', null, 0),
            array('used_for_sort_by', null, 0)
        ),
        \Magento\Index\Model\Event::TYPE_SAVE,
        true
    ), //Event Type, result
    array(
        true,
        false, //Attribute, isAddFilterable
        //Original attribute data
        array(
            array('backend_type', 'not_static'),
            array('is_filterable', 0),
            array('used_in_product_listing', 0),
            array('is_used_for_promo_rules', 0),
            array('used_for_sort_by', 0)
        ),
        //Attribute data
        array(
            array('backend_type', null, 'not_static'),
            array('is_filterable', null, 0),
            array('used_in_product_listing', null, 1),
            array('is_used_for_promo_rules', null, 0),
            array('used_for_sort_by', null, 0)
        ),
        \Magento\Index\Model\Event::TYPE_SAVE,
        true
    ), //Event Type, result
    array(
        true,
        false, //Attribute, isAddFilterable
        //Original attribute data
        array(
            array('backend_type', 'not_static'),
            array('is_filterable', 0),
            array('used_in_product_listing', 0),
            array('is_used_for_promo_rules', 1),
            array('used_for_sort_by', 0)
        ),
        //Attribute data
        array(
            array('backend_type', null, 'not_static'),
            array('is_filterable', null, 0),
            array('used_in_product_listing', null, 0),
            array('is_used_for_promo_rules', null, 0),
            array('used_for_sort_by', null, 0)
        ),
        \Magento\Index\Model\Event::TYPE_SAVE,
        true
    ), //Event Type, result
    array(
        true,
        false,
        array(
            array('backend_type', 'not_static'),
            array('is_filterable', 0),
            array('used_in_product_listing', 0),
            array('is_used_for_promo_rules', 0),
            array('used_for_sort_by', 0)
        ),
        array(
            array('backend_type', null, 'not_static'),
            array('is_filterable', null, 0),
            array('used_in_product_listing', null, 0),
            array('is_used_for_promo_rules', null, 1),
            array('used_for_sort_by', null, 0)
        ),
        \Magento\Index\Model\Event::TYPE_SAVE,
        true
    ),
    array(
        true,
        false, //Attribute, isAddFilterable
        //Original attribute data
        array(
            array('backend_type', 'not_static'),
            array('is_filterable', 0),
            array('used_in_product_listing', 0),
            array('is_used_for_promo_rules', 0),
            array('used_for_sort_by', 1)
        ),
        //Attribute data
        array(
            array('backend_type', null, 'not_static'),
            array('is_filterable', null, 0),
            array('used_in_product_listing', null, 0),
            array('is_used_for_promo_rules', null, 0),
            array('used_for_sort_by', null, 0)
        ),
        \Magento\Index\Model\Event::TYPE_SAVE,
        true
    ), //Event Type, result
    array(
        true,
        false, //Attribute, isAddFilterable
        //Original attribute data
        array(
            array('backend_type', 'not_static'),
            array('is_filterable', 0),
            array('used_in_product_listing', 0),
            array('is_used_for_promo_rules', 0),
            array('used_for_sort_by', 0)
        ),
        //Attribute data
        array(
            array('backend_type', null, 'not_static'),
            array('is_filterable', null, 0),
            array('used_in_product_listing', null, 0),
            array('is_used_for_promo_rules', null, 0),
            array('used_for_sort_by', null, 1)
        ),
        \Magento\Index\Model\Event::TYPE_SAVE,
        true
    ) //Event Type, result
);
