<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    'comment' => 'comment',
    'nodes' => [
        [
            'nodeName' => 'tab',
            '@attributes' => ['id' => 'tab_1', 'sortOrder' => 10, 'class' => 'css class'],
            'parameters' => [['name' => 'label', '#text' => 'tab label']],
        ],
        [
            'nodeName' => 'section',
            '@attributes' => ['id' => 'section_1', 'sortOrder' => 10, 'type' => 'text'],
            'parameters' => [
                ['name' => 'class', '#text' => 'css class'],
                ['name' => 'label', '#text' => 'section label'],
                ['name' => 'resource', '#text' => 'Magento_Adminhtml::acl'],
                ['name' => 'header_css', '#text' => 'some css class'],
                ['name' => 'tab', '#text' => 'tab_1'],
            ],
            'subConfig' => [
                [
                    'nodeName' => 'group',
                    '@attributes' => ['id' => 'group_1', 'sortOrder' => 10, 'type' => 'text'],
                    'parameters' => [
                        ['name' => 'label', '#text' => 'group label'],
                        ['name' => 'comment', '#cdata-section' => 'group comment'],
                        ['name' => 'fieldset_css', '#text' => 'some css class'],
                        ['name' => 'clone_fields', '#text' => '1'],
                        ['name' => 'clone_model', '#text' => 'Magento\Some\Model\Name'],
                        ['name' => 'help_url', '#text' => 'some_url'],
                        ['name' => 'hide_in_single_store_mode', '#text' => '1'],
                        ['name' => 'expanded', '#text' => '1'],
                    ],
                    'subConfig' => [
                        [
                            'nodeName' => 'field',
                            '@attributes' => ['id' => 'field_1'],
                            'parameters' => [
                                ['name' => 'comment', '#cdata-section' => 'comment_test'],
                                ['name' => 'tooltip', '#text' => 'tooltip_test'],
                                ['name' => 'frontend_class', '#text' => 'frontend_class_test'],
                                ['name' => 'validate', '#text' => 'validate_test'],
                                ['name' => 'can_be_empty', '#text' => '1'],
                                ['name' => 'if_module_enabled', '#text' => 'Magento_Backend'],
                                ['name' => 'frontend_model', '#text' => 'Magento\Some\Model\Name'],
                                ['name' => 'backend_model', '#text' => 'Magento\Some\Model\Name'],
                                ['name' => 'source_model', '#text' => 'Magento\Some\Model\Name'],
                                ['name' => 'config_path', '#text' => 'config/path/test'],
                                ['name' => 'base_url', '#text' => 'some_url'],
                                ['name' => 'upload_dir', '#text' => 'some_directory'],
                                ['name' => 'button_url', '#text' => 'some_url'],
                                ['name' => 'button_label', '#text' => 'some_label'],
                                [
                                    'name' => 'depends',
                                    'subConfig' => [
                                        [
                                            'nodeName' => 'field',
                                            '@attributes' => ['id' => 'module1'],
                                            '#text' => 'yes',
                                        ],
                                    ]
                                ],
                                ['name' => 'more_url', '#text' => 'more_url_test'],
                                ['name' => 'demo_url', '#text' => 'demo_url_test'],
                                [
                                    '@attributes' => ['type' => 'undefined', 'some' => 'attribute'],
                                    'name' => 'attribute',
                                    '#text' => 'undefined_test'
                                ],
                                [
                                    '@attributes' => ['type' => 'node'],
                                    'name' => 'attribute',
                                    'subConfig' => [
                                        [
                                            'nodeName' => 'label',
                                            'subConfig' => [
                                                ['nodeName' => 'nodeLabel', '#text' => 'nodeValue'],
                                            ],
                                        ],
                                    ]
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        ],
    ]
];
