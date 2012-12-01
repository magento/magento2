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
 * @package     Mage_Backend
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 

return array(
    'config' => array(
        'system' => array(
            'tabs' => array(
                'tab_1' => array(
                    'id' => 'tab_1',
                    'label' => 'Tab 1 New'
                )
            ),
            'sections' => array(
                'section_1' => array(
                    'id' => 'section_1',
                    'type' => 'text',
                    'label' => 'Section 1 New',
                    'tab' => 'tab_1',
                    'groups' => array(
                        'group_1' => array(
                            'id' => 'group_1',
                            'type' => 'text',
                            'label' => 'Group 1 New',
                            'fields' => array(
                                'field_2' => array(
                                    'id' => 'field_2',
                                    'translate' => 'label',
                                    'showInWebsite' => '1',
                                    'type' => 'text',
                                    'label' => 'Field 2',
                                    'backend_model' => 'Mage_Backend_Model_Config_Backend_Encrypted'
                                )
                            ),
                        ),
                        'group_2' => array(
                            'id' => 'group_2',
                            'type' => 'text',
                            'label' => 'Group 2',
                            'fields' => array(
                                'field_3' => array(
                                    'id' => 'field_3',
                                    'translate' => 'label',
                                    'showInWebsite' => '1',
                                    'type' => 'text',
                                    'label' => 'Field 3',
                                )
                            ),
                        )
                    ),
                ),
                'section_2' => array(
                    'id' => 'section_2',
                    'type' => 'text',
                    'label' => 'Section 2',
                    'tab' => 'tab_2',
                    'groups' => array(
                        'group_3' => array(
                            'id' => 'group_3',
                            'type' => 'text',
                            'label' => 'Group 3',
                            'comment' => '<a href="test_url">test_link</a>',
                            'fields' => array(
                                'field_3' => array(
                                    'id' => 'field_3',
                                    'translate' => 'label',
                                    'showInWebsite' => '1',
                                    'type' => 'text',
                                    'label' => 'Field 3',
                                    'attribute_0' => array(
                                        'someArr' => array(
                                            'someVal' => 1
                                        )
                                    ),
                                    'depends' => array(
                                        'fields' => array(
                                            'field_4' => array(
                                                'id' => 'field_4',
                                                'value' => 'someValue'
                                            ),
                                            'field_1' => array(
                                                'id' => 'field_1',
                                                'value' => 'someValue'
                                            )
                                        )
                                    )
                                ),
                                'field_4' => array(
                                    'id' => 'field_4',
                                    'translate' => 'label',
                                    'showInWebsite' => '1',
                                    'type' => 'text',
                                    'label' => 'Field 4',
                                    'backend_model' => 'Mage_Backend_Model_Config_Backend_Encrypted',
                                    'attribute_1' => 'test_value_1',
                                    'attribute_2' => 'test_value_2',
                                    'attribute_text' => '<test_value>',
                                    'attribute_text_in_array' => array(
                                        'var' => '<a href="test_url">test_link</a>',
                                        'type' => 'someType'
                                    ),
                                    'depends' => array(
                                        'fields' => array(
                                            'field_3' => array(
                                                'id' => 'field_3',
                                                'value' => 0
                                            )
                                        )
                                    )
                                )
                            ),
                        )
                    )
                )
            )
        )
    )
);
