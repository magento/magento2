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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


return array(
    'create_text_api' => array(
        'attribute_code' => 'a_text_api',
        'scope' => 'store',
        'frontend_input' => 'text',
        'default_value' => '',
        'is_unique' => '0',
        'is_required' => '0',
        'apply_to' => array(
            'simple',
            'grouped',
        ),
        'is_configurable' => '0',
        'is_searchable' => '1',
        'is_visible_in_advanced_search' => '0',
        'is_comparable' => '1',
        'is_used_for_promo_rules' => '0',
        'is_visible_on_front' => '1',
        'used_in_product_listing' => '0',
        //'label' => 'a_text_api',
        'frontend_label' => array(
            array(
                'store_id' => 0,
                'label' => 'a_text_api'
            ),
            array(
                'store_id' => 1,
                'label' => 'a_text_api'
            ),
        ),
    ),
    'create_select_api' => array(
        'attribute_code' => 'a_select_api',
        'scope' => 'store',
        'frontend_input' => 'select',
        'default_value' => '',
        'is_unique' => '0',
        'is_required' => '0',
        'apply_to' => array(
            'simple',
            'grouped',
        ),
        'is_configurable' => '0',
        'is_searchable' => '1',
        'is_visible_in_advanced_search' => '0',
        'is_comparable' => '1',
        'is_used_for_promo_rules' => '0',
        'is_visible_on_front' => '1',
        'used_in_product_listing' => '0',
        //'label' => 'a_select_api',
        'frontend_label' => array(
            array(
                'store_id' => 0,
                'label' => 'a_select_api'
            ),
            array(
                'store_id' => 1,
                'label' => 'a_select_api'
            ),
        ),
    ),
    'create_text_installer' => array(
        'code' => 'a_text_ins',
        'attributeData' => array(
            'type' => 'varchar',
            'input' => 'text',
            'label' => 'a_text_ins',
            'required' => 0,
            'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'user_defined' => true,

        ),
    ),
    'create_select_installer' => array(
        'code' => 'a_select_ins',
        'attributeData' => array(
            'type' => 'int',
            'input' => 'select',
            'label' => 'a_select_ins',
            'required' => 0,
            'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'user_defined' => true,
            'option' => array(
                'values' => array(
                    'option1',
                    'option2',
                    'option3',
                ),
            )
        ),
    ),
    'create_select_api_options' => array(
        array(
            'label' => array(
                array(
                    'store_id' => 0,
                    'value' => 'option1'
                ),
                array(
                    'store_id' => 1,
                    'value' => 'option1'
                ),
            ),
            'order' => 0,
            'is_default' => 1
        ),
        array(
            'label' => array(
                array(
                    'store_id' => 0,
                    'value' => 'option2'
                ),
                array(
                    'store_id' => 1,
                    'value' => 'option2'
                ),
            ),
            'order' => 0,
            'is_default' => 1
        ),
    ),
);

