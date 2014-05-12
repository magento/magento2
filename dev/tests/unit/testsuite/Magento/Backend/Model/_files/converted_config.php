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
    'config' => array(
        'noNamespaceSchemaLocation' => '../../../../../../../../app/code/Magento/Backend/etc/system_file.xsd',
        'system' => array(
            'tabs' => array(
                'tab_1' => array(
                    'id' => 'tab_1',
                    'label' => 'Tab 1 New',
                    '_elementType' => 'tab',
                ),
            ),
            'sections' => array(
                'section_1' => array(
                    'id' => 'section_1',
                    'type' => 'text',
                    'label' => 'Section 1 New',
                    'tab' => 'tab_1',
                    'children' => array(
                        'group_1' => array(
                            'id' => 'group_1',
                            'type' => 'text',
                            'label' => 'Group 1 New',
                            'children' => array(
                                'field_2' => array(
                                    'id' => 'field_2',
                                    'translate' => 'label',
                                    'showInWebsite' => '1',
                                    'type' => 'text',
                                    'label' => 'Field 2',
                                    'backend_model' => 'Magento\Backend\Model\Config\Backend\Encrypted',
                                    '_elementType' => 'field',
                                ),
                            ),
                            '_elementType' => 'group',
                        ),
                        'group_level_1' => array(
                            'id' => 'group_level_1',
                            'type' => 'text',
                            'label' => 'Group Level 1',
                            'children' => array(
                                'field_3' => array(
                                    'id' => 'field_3',
                                    'translate' => 'label',
                                    'showInWebsite' => '1',
                                    'type' => 'text',
                                    'label' => 'Field 3',
                                    '_elementType' => 'field',
                                ),
                                'group_level_2' => array(
                                    'id' => 'group_level_2',
                                    'type' => 'text',
                                    'label' => 'Group Level 2',
                                    'children' => array(
                                        'field_3_1' => array(
                                            'id' => 'field_3_1',
                                            'translate' => 'label',
                                            'showInWebsite' => '1',
                                            'type' => 'text',
                                            'label' => 'Field 3.1',
                                            '_elementType' => 'field',
                                        ),
                                        'group_level_3' => array(
                                            'id' => 'group_level_3',
                                            'type' => 'text',
                                            'label' => 'Group Level 3',
                                            'children' => array(
                                                'field_3_1_1' => array(
                                                    'id' => 'field_3_1_1',
                                                    'translate' => 'label',
                                                    'showInWebsite' => '1',
                                                    'backend_model' => 'Magento\Backend\Model\Config\Backend\Encrypted',
                                                    'type' => 'text',
                                                    'label' => 'Field 3.1.1',
                                                    '_elementType' => 'field',
                                                ),
                                            ),
                                            '_elementType' => 'group',
                                        ),
                                    ),
                                    '_elementType' => 'group',
                                ),
                            ),
                            '_elementType' => 'group',
                        ),
                    ),
                    '_elementType' => 'section',
                ),
                'section_2' => array(
                    'id' => 'section_2',
                    'type' => 'text',
                    'label' => 'Section 2',
                    'tab' => 'tab_2',
                    'children' => array(
                        'group_3' => array(
                            'id' => 'group_3',
                            'type' => 'text',
                            'label' => 'Group 3',
                            'comment' => '<a href="test_url">test_link</a>',
                            'children' => array(
                                'field_3' => array(
                                    'id' => 'field_3',
                                    'translate' => 'label',
                                    'showInWebsite' => '1',
                                    'type' => 'text',
                                    'label' => 'Field 3',
                                    'attribute_0' => array(
                                        'someArr' => array(
                                            'someVal' => '1',
                                        ),
                                    ),
                                    'depends' => array(
                                        'fields' => array(
                                            'field_4' => array(
                                                'id' => 'field_4',
                                                'value' => 'someValue',
                                                '_elementType' => 'field',
                                            ),
                                            'field_1' => array(
                                                'id' => 'field_1',
                                                'value' => 'someValue',
                                                '_elementType' => 'field',
                                            ),
                                        ),
                                    ),
                                    '_elementType' => 'field',
                                ),
                                'field_4' => array(
                                    'id' => 'field_4',
                                    'translate' => 'label',
                                    'showInWebsite' => '1',
                                    'type' => 'text',
                                    'label' => 'Field 4',
                                    'backend_model' => 'Magento\Backend\Model\Config\Backend\Encrypted',
                                    'attribute_1' => 'test_value_1',
                                    'attribute_2' => 'test_value_2',
                                    'attribute_text' => '<test_value>',
                                    'attribute_text_in_array' => array(
                                        'var' => '<a href="test_url">test_link</a>',
                                        'type' => 'someType',
                                    ),
                                    'depends' => array(
                                        'fields' => array(
                                            'field_3' => array(
                                                'id' => 'field_3',
                                                'value' => '0',
                                                '_elementType' => 'field',
                                            ),
                                        ),
                                    ),
                                    '_elementType' => 'field',
                                ),
                                 'field_5' => array(
                                    'id' => 'field_5',
                                    'translate' => 'label',
                                    'showInWebsite' => '1',
                                    'type' => 'text',
                                    'label' => '',
                                    '_elementType' => 'field',
                                ),
                            ),
                            '_elementType' => 'group',
                        ),
                        'group_4' => array(
                            'id' => 'group_4',
                            'label' => 'Group 4',
                            'type' => 'text',
                            'showInDefault' => 1,
                            'showInStore' => 1,
                            'showInWebsite' => 1,
                            'depends' => array(
                                'fields' => array(
                                    'section_2/group_3/field_5' => array(
                                        'id' => 'section_2/group_3/field_5',
                                        'value' => 1,
                                        '_elementType' => 'field'
                                    ),
                                )
                            ),
                            '_elementType' => 'group',
                        )
                    ),
                    '_elementType' => 'section',
                ),
            ),
        ),
    ),
);
