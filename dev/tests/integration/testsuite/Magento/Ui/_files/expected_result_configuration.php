<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return ['arguments' => [
    'data' => [
        'name' => 'data',
        'xsi:type' => 'array',
        'item' => [
            'config' => [
                'name' => 'config',
                'xsi:type' => 'array',
                'item' => [
                    'component' => [
                        'name' => 'component',
                        'xsi:type' => 'string',
                        'value' => 'uiComponent2',
                    ],
                ],
            ],
            'save_parameters_in_session' => [
                'name' => 'save_parameters_in_session',
                'xsi:type' => 'string',
                'value' => '1',
            ],
            'client_root' => [
                'name' => 'client_root',
                'xsi:type' => 'string',
                'value' => 'mui/index/render',
            ],
            'template' => [
                'name' => 'template',
                'xsi:type' => 'string',
                'value' => 'templates/listing/default',
            ],
            'spinner' => [
                'name' => 'spinner',
                'xsi:type' => 'string',
                'value' => 'columns',
            ],
            'js_config' => [
                'name' => 'js_config',
                'xsi:type' => 'array',
                'item' => [
                    'deps' => [
                        'name' => 'deps',
                        'xsi:type' => 'array',
                        'item' => [
                            0 => [
                                'name' => 0,
                                'xsi:type' => 'string',
                                'value' => 'test_component.test_component_data_source',
                            ],
                        ],
                    ],
                    'provider' => [
                        'name' => 'provider',
                        'xsi:type' => 'string',
                        'value' => 'test_component.test_component_data_source',
                    ],
                ],
            ],
        ],
    ],
],
    'attributes' => [
        'sorting' => 'true',
        'class' => 'Some_Listing_Class_Two',
        'component' => 'uiComponent2',
        'extends' => 'parent_component',
    ],
    'children' => [
        'test_component_data_source' => [
            'attributes' => [
                'class' => 'Some_DataSource_Class',
                'name' => 'test_component_data_source',
                'component' => 'Magento_Test/js/grid/provider',
            ],
            'children' => [],
            'arguments' => [
                'dataProvider' => [
                    'name' => 'dataProvider',
                    'xsi:type' => 'configurableObject',
                    'argument' => [
                        'data' => [
                            'name' => 'data',
                            'xsi:type' => 'array',
                            'item' => [
                                'config' => [
                                    'name' => 'config',
                                    'xsi:type' => 'array',
                                    'item' => [
                                        'update_url' => [
                                            'name' => 'update_url',
                                            'xsi:type' => 'url',
                                            'path' => 'mui/index/render',
                                        ],
                                        'component' => [
                                            'name' => 'component',
                                            'xsi:type' => 'string',
                                            'value' => 'Magento_Test/js/grid/provider',
                                        ],
                                        'storageConfig' => [
                                            'name' => 'storageConfig',
                                            'xsi:type' => 'array',
                                            'item' => [
                                                'indexField' => [
                                                    'name' => 'indexField',
                                                    'xsi:type' => 'string',
                                                    'value' => 'identity',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'class' => [
                            'name' => 'class',
                            'xsi:type' => 'string',
                            'value' => 'Magento_Test\\DataProvider',
                        ],
                        'name' => [
                            'name' => 'name',
                            'xsi:type' => 'string',
                            'value' => 'test_component_data_source',
                        ],
                        'primaryFieldName' => [
                            'name' => 'primaryFieldName',
                            'xsi:type' => 'string',
                            'value' => 'identity',
                        ],
                        'requestFieldName' => [
                            'name' => 'requestFieldName',
                            'xsi:type' => 'string',
                            'value' => 'identity',
                        ],
                    ],
                ],
            ],
        ],
        'columns' => [
            'arguments' => [
                'data' => [
                    'name' => 'data',
                    'xsi:type' => 'array',
                    'item' => [
                        'config' => [
                            'name' => 'config',
                            'xsi:type' => 'array',
                            'item' => [
                                'component' => [
                                    'name' => 'component',
                                    'xsi:type' => 'string',
                                    'value' => 'Magento_Test/js/grid/listing',
                                ],
                                'template' => [
                                    'name' => 'template',
                                    'xsi:type' => 'string',
                                    'value' => 'Magento_Test/grid/listing',
                                ],
                                'link' => [
                                    'name' => 'link',
                                    'xsi:type' => 'url',
                                    'path' => 'bulk/index',
                                ],
                                'linkText' => [
                                    'name' => 'linkText',
                                    'xsi:type' => 'string',
                                    'translate' => 'true',
                                    'value' => 'Bulk Actions Log',
                                ],
                                'dismissAllText' => [
                                    'name' => 'dismissAllText',
                                    'xsi:type' => 'string',
                                    'translate' => 'true',
                                    'value' => 'Dismiss All Completed Tasks',
                                ],
                                'dismissUrl' => [
                                    'name' => 'dismissUrl',
                                    'xsi:type' => 'url',
                                    'path' => 'bulk/notification/dismiss',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'attributes' => [
                'name' => 'columns',
                'component' => 'Magento_Test/js/grid/listing',
                'template' => 'Magento_Test/grid/listing',
            ],
            'children' => [
                'created_at' => [
                    'arguments' => [
                        'data' => [
                            'name' => 'data',
                            'xsi:type' => 'array',
                            'item' => [
                                'config' => [
                                    'name' => 'config',
                                    'xsi:type' => 'array',
                                    'item' => [
                                        'sorting' => [
                                            'name' => 'sorting',
                                            'xsi:type' => 'string',
                                            'value' => 'desc',
                                        ],
                                        'label' => [
                                            'name' => 'label',
                                            'translate' => 'true',
                                            'xsi:type' => 'string',
                                            'value' => 'Some Label',
                                        ],
                                        'dataType' => [
                                            'name' => 'dataType',
                                            'xsi:type' => 'string',
                                            'value' => 'date',
                                        ],
                                        'component' => [
                                            'name' => 'component',
                                            'xsi:type' => 'string',
                                            'value' => 'Magento_Test2/js/grid/columns/message',
                                        ],
                                        'sortOrder' => [
                                            'name' => 'sortOrder',
                                            'xsi:type' => 'number',
                                            'value' => '50',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'attributes' => [
                        'name' => 'created_at',
                        'component' => 'Magento_Test2/js/grid/columns/message',
                        'sortOrder' => '50',
                    ],
                    'children' => [],
                ],
                'dismiss' => [
                    'arguments' => [
                        'data' => [
                            'name' => 'data',
                            'xsi:type' => 'array',
                            'item' => [
                                'config' => [
                                    'name' => 'config',
                                    'xsi:type' => 'array',
                                    'item' => [
                                        'indexField' => [
                                            'name' => 'indexField',
                                            'xsi:type' => 'string',
                                            'value' => 'identity',
                                        ],
                                        'bodyTmpl' => [
                                            'name' => 'bodyTmpl',
                                            'xsi:type' => 'string',
                                            'value' => 'Magento_Test/grid/cells/actions',
                                        ],
                                        'sortOrder' => [
                                            'name' => 'sortOrder',
                                            'xsi:type' => 'number',
                                            'value' => '10',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'attributes' => [
                        'name' => 'dismiss',
                        'class' => 'Some_Actions_Class',
                        'sortOrder' => '10',
                    ],
                    'children' => [],
                ],
            ],
        ],
        'modalContainer' => [
            'attributes' => [
                'name' => 'modalContainer',
            ],
            'children' => [
                'modal' => [
                    'attributes' => [
                        'name' => 'modal',
                    ],
                    'children' => [
                        'insertBulk' => [
                            'arguments' => [
                                'data' => [
                                    'name' => 'data',
                                    'xsi:type' => 'array',
                                    'item' => [
                                        'config' => [
                                            'name' => 'config',
                                            'xsi:type' => 'array',
                                            'item' => [
                                                'externalProvider' => [
                                                    'name' => 'externalProvider',
                                                    'xsi:type' => 'string',
                                                    'value' => '${ $.ns }.bulk_details_form_modal_data_source',
                                                ],
                                                'ns' => [
                                                    'name' => 'ns',
                                                    'xsi:type' => 'string',
                                                    'value' => 'bulk_details_form_modal',
                                                ],
                                                'toolbarContainer' => [
                                                    'name' => 'toolbarContainer',
                                                    'xsi:type' => 'string',
                                                    'value' => '${ $.parentName }',
                                                ],
                                                'formSubmitType' => [
                                                    'name' => 'formSubmitType',
                                                    'xsi:type' => 'string',
                                                    'value' => 'ajax',
                                                ],
                                                'loading' => [
                                                    'name' => 'loading',
                                                    'xsi:type' => 'boolean',
                                                    'value' => 'false',
                                                ],
                                                'render_url' => [
                                                    'name' => 'render_url',
                                                    'xsi:type' => 'url',
                                                    'param' => [
                                                        'handle' => [
                                                            'name' => 'handle',
                                                            'value' => 'bulk_bulk_details_modal',
                                                        ],
                                                        'buttons' => [
                                                            'name' => 'buttons',
                                                            'value' => '1',
                                                        ],
                                                    ],
                                                    'path' => 'mui/index/render_handle',
                                                ],
                                                'columnsProvider' => [
                                                    'name' => 'columnsProvider',
                                                    'xsi:type' => 'string',
                                                    'value' => 'ns = test_component, index = columns',
                                                ],
                                                'component' => [
                                                    'name' => 'component',
                                                    'xsi:type' => 'string',
                                                    'value' => 'Magento_Test/js/insert-form',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'attributes' => [
                                'name' => 'insertBulk',
                                'component' => 'Magento_Test/js/insert-form',
                            ],
                            'children' => [],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
