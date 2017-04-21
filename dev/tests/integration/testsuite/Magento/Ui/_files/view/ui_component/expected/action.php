<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'arguments' => [
        'data' => [
            'name' => 'data',
            'xsi:type' => 'array',
            'item' => [
                'config' => [
                    'name' => 'config',
                    'xsi:type' => 'array',
                    'item' => [
                        'label' => [
                            'name' => 'label',
                            'translate' => 'true',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'type' => [
                            'name' => 'type',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'url' => [
                            'name' => 'url',
                            'xsi:type' => 'url',
                            'param' => [
                                'string' => [
                                    'name' => 'string',
                                    'value' => 'value',
                                ],
                            ],
                            'path' => 'anySimpleType',
                        ],
                        'confirm' => [
                            'name' => 'confirm',
                            'xsi:type' => 'array',
                            'item' => [
                                'title' => [
                                    'name' => 'title',
                                    'translate' => 'true',
                                    'xsi:type' => 'string',
                                    'value' => 'string',
                                ],
                                'message' => [
                                    'name' => 'message',
                                    'translate' => 'true',
                                    'xsi:type' => 'string',
                                    'value' => 'string',
                                ],
                            ],
                        ],
                        'callback' => [
                            'name' => 'callback',
                            'xsi:type' => 'array',
                            'item' => [
                                'provider' => [
                                    'name' => 'provider',
                                    'translate' => 'true',
                                    'xsi:type' => 'string',
                                    'value' => 'string',
                                ],
                                'target' => [
                                    'name' => 'target',
                                    'translate' => 'true',
                                    'xsi:type' => 'string',
                                    'value' => 'string',
                                ],
                            ],
                        ],
                        'provider' => [
                            'name' => 'provider',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'component' => [
                            'name' => 'component',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'template' => [
                            'name' => 'template',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'sortOrder' => [
                            'name' => 'sortOrder',
                            'xsi:type' => 'number',
                            'value' => '0',
                        ],
                        'displayArea' => [
                            'name' => 'displayArea',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'storageConfig' => [
                            'name' => 'storageConfig',
                            'xsi:type' => 'array',
                            'item' => [
                                'provider' => [
                                    'name' => 'provider',
                                    'xsi:type' => 'string',
                                    'value' => 'string',
                                ],
                                'namespace' => [
                                    'name' => 'namespace',
                                    'xsi:type' => 'string',
                                    'value' => 'string',
                                ],
                                'path' => [
                                    'name' => 'path',
                                    'xsi:type' => 'url',
                                    'param' => [
                                        'string' => [
                                            'name' => 'string',
                                            'value' => 'string',
                                        ],
                                    ],
                                    'path' => 'string',
                                ],
                            ],
                        ],
                        'statefull' => [
                            'name' => 'statefull',
                            'xsi:type' => 'array',
                            'item' => [
                                'anySimpleType' => [
                                    'name' => 'anySimpleType',
                                    'xsi:type' => 'boolean',
                                ],
                            ],
                        ],
                        'imports' => [
                            'name' => 'imports',
                            'xsi:type' => 'array',
                            'item' => [
                                'string' => [
                                    'name' => 'string',
                                    'xsi:type' => 'string',
                                    'value' => 'string',
                                ],
                            ],
                        ],
                        'exports' => [
                            'name' => 'exports',
                            'xsi:type' => 'array',
                            'item' => [
                                'string' => [
                                    'name' => 'string',
                                    'xsi:type' => 'string',
                                    'value' => 'string',
                                ],
                            ],
                        ],
                        'links' => [
                            'name' => 'links',
                            'xsi:type' => 'array',
                            'item' => [
                                'string' => [
                                    'name' => 'string',
                                    'xsi:type' => 'string',
                                    'value' => 'string',
                                ],
                            ],
                        ],
                        'listens' => [
                            'name' => 'listens',
                            'xsi:type' => 'array',
                            'item' => [
                                'string' => [
                                    'name' => 'string',
                                    'xsi:type' => 'string',
                                    'value' => 'string',
                                ],
                            ],
                        ],
                        'ns' => [
                            'name' => 'ns',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'componentType' => [
                            'name' => 'componentType',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'dataScope' => [
                            'name' => 'dataScope',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                    ],
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
                                    'value' => 'string',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'actions' => [
            'name' => 'actions',
            'xsi:type' => 'array',
            'item' => [
                'action' => [
                    'name' => 'action',
                    'xsi:type' => 'array',
                    'item' => [
                        'type' => [
                            'value' => 'some_type',
                            'name' => 'type',
                            'xsi:type' => 'string',
                        ],
                        'url' => [
                            'name' => 'url',
                            'xsi:type' => 'url',
                            'param' => [
                                'key1' => [
                                    'name' => 'key1',
                                    'value' => 'value1',
                                ],
                                'key2' => [
                                    'name' => 'key2',
                                    'value' => 'value2',
                                ],
                            ],
                            'path' => 'some_url',
                        ],
                        'label' => [
                            'value' => 'Translate Label',
                            'name' => 'label',
                            'xsi:type' => 'string',
                            'translate' => 'true',
                        ],
                        'custom_param' => [
                            'value' => 'custom_value',
                            'name' => 'custom_param',
                            'xsi:type' => 'string',
                        ],
                    ],
                ],
            ],
        ],
    ],
    'children' => [],
    'uiComponentType' => 'action',
];
