<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    'config' => [
        'noNamespaceSchemaLocation' => 'urn:magento:module:Magento_Config:etc/system_file.xsd',
        'system' => [
            'tabs' => [
                'tab_1' => [
                    'id' => 'tab_1',
                    'label' => 'Tab 1 New',
                    '_elementType' => 'tab',
                ],
            ],
            'sections' => [
                'section_1' => [
                    'id' => 'section_1',
                    'type' => 'text',
                    'label' => 'Section 1 New',
                    'tab' => 'tab_1',
                    'children' => [
                        'group_1' => [
                            'id' => 'group_1',
                            'type' => 'text',
                            'label' => 'Group 1 New',
                            'children' => [
                                'field_2' => [
                                    'id' => 'field_2',
                                    'translate' => 'label',
                                    'showInWebsite' => '1',
                                    'type' => 'text',
                                    'label' => 'Field 2',
                                    'backend_model' => 'Magento\Config\Model\Config\Backend\Encrypted',
                                    '_elementType' => 'field',
                                ],
                            ],
                            '_elementType' => 'group',
                        ],
                        'group_level_1' => [
                            'id' => 'group_level_1',
                            'type' => 'text',
                            'label' => 'Group Level 1',
                            'children' => [
                                'field_3' => [
                                    'id' => 'field_3',
                                    'translate' => 'label',
                                    'showInWebsite' => '1',
                                    'type' => 'text',
                                    'label' => 'Field 3',
                                    '_elementType' => 'field',
                                ],
                                'group_level_2' => [
                                    'id' => 'group_level_2',
                                    'type' => 'text',
                                    'label' => 'Group Level 2',
                                    'children' => [
                                        'field_3_1' => [
                                            'id' => 'field_3_1',
                                            'translate' => 'label',
                                            'showInWebsite' => '1',
                                            'type' => 'text',
                                            'label' => 'Field 3.1',
                                            '_elementType' => 'field',
                                        ],
                                        'group_level_3' => [
                                            'id' => 'group_level_3',
                                            'type' => 'text',
                                            'label' => 'Group Level 3',
                                            'children' => [
                                                'field_3_1_1' => [
                                                    'id' => 'field_3_1_1',
                                                    'translate' => 'label',
                                                    'showInWebsite' => '1',
                                                    'backend_model' => 'Magento\Config\Model\Config\Backend\Encrypted',
                                                    'type' => 'text',
                                                    'label' => 'Field 3.1.1',
                                                    '_elementType' => 'field',
                                                ],
                                            ],
                                            '_elementType' => 'group',
                                        ],
                                    ],
                                    '_elementType' => 'group',
                                ],
                            ],
                            '_elementType' => 'group',
                        ],
                    ],
                    '_elementType' => 'section',
                ],
                'section_2' => [
                    'id' => 'section_2',
                    'type' => 'text',
                    'label' => 'Section 2',
                    'tab' => 'tab_2',
                    'children' => [
                        'group_3' => [
                            'id' => 'group_3',
                            'type' => 'text',
                            'label' => 'Group 3',
                            'comment' => '<a href="test_url">test_link</a>',
                            'children' => [
                                'field_3' => [
                                    'id' => 'field_3',
                                    'translate' => 'label',
                                    'showInWebsite' => '1',
                                    'type' => 'text',
                                    'label' => 'Field 3',
                                    'attribute_0' => [
                                        'someArr' => [
                                            'someVal' => '1',
                                        ],
                                    ],
                                    'depends' => [
                                        'fields' => [
                                            'field_4' => [
                                                'id' => 'field_4',
                                                'value' => 'someValue',
                                                '_elementType' => 'field',
                                            ],
                                            'field_1' => [
                                                'id' => 'field_1',
                                                'value' => 'someValue',
                                                '_elementType' => 'field',
                                            ],
                                        ],
                                    ],
                                    '_elementType' => 'field',
                                ],
                                'field_4' => [
                                    'id' => 'field_4',
                                    'translate' => 'label',
                                    'showInWebsite' => '1',
                                    'type' => 'text',
                                    'label' => 'Field 4',
                                    'backend_model' => 'Magento\Config\Model\Config\Backend\Encrypted',
                                    'attribute_1' => 'test_value_1',
                                    'attribute_2' => 'test_value_2',
                                    'attribute_text' => '<test_value>',
                                    'attribute_text_in_array' => [
                                        'var' => '<a href="test_url">test_link</a>',
                                        'type' => 'someType',
                                    ],
                                    'depends' => [
                                        'fields' => [
                                            'field_3' => [
                                                'id' => 'field_3',
                                                'value' => '0',
                                                '_elementType' => 'field',
                                            ],
                                        ],
                                    ],
                                    '_elementType' => 'field',
                                ],
                                 'field_5' => [
                                    'id' => 'field_5',
                                    'translate' => 'label',
                                    'showInWebsite' => '1',
                                    'type' => 'text',
                                    'label' => '',
                                    '_elementType' => 'field',
                                ],
                            ],
                            '_elementType' => 'group',
                        ],
                        'group_4' => [
                            'id' => 'group_4',
                            'label' => 'Group 4',
                            'type' => 'text',
                            'showInDefault' => 1,
                            'showInStore' => 1,
                            'showInWebsite' => 1,
                            'depends' => [
                                'fields' => [
                                    'section_2/group_3/field_5' => [
                                        'id' => 'section_2/group_3/field_5',
                                        'value' => 1,
                                        '_elementType' => 'field',
                                    ],
                                ],
                            ],
                            '_elementType' => 'group',
                        ],
                    ],
                    '_elementType' => 'section',
                ],
            ],
        ],
    ],
];
