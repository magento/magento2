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
                'layout' => [
                    'name' => 'layout',
                    'xsi:type' => 'array',
                    'item' => [
                        'type' => [
                            'name' => 'type',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'navContainerName' => [
                            'name' => 'navContainerName',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                    ],
                ],
                'config' => [
                    'name' => 'config',
                    'xsi:type' => 'array',
                    'item' => [
                        'selectorPrefix' => [
                            'name' => 'selectorPrefix',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'messagesClass' => [
                            'name' => 'messagesClass',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'errorClass' => [
                            'name' => 'errorClass',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'ajaxSaveType' => [
                            'name' => 'ajaxSaveType',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'namespace' => [
                            'name' => 'namespace',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'ajaxSave' => [
                            'name' => 'ajaxSave',
                            'xsi:type' => 'boolean',
                            'value' => 'false',
                        ],
                        'reloadItem' => [
                            'name' => 'reloadItem',
                            'xsi:type' => 'string',
                            'value' => 'string',
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
                                    'value' => 'true',
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
                'buttons' => [
                    'name' => 'buttons',
                    'xsi:type' => 'array',
                    'item' => [
                        'string' => [
                            'name' => 'string',
                            'xsi:type' => 'array',
                            'item' => [
                                'label' => [
                                    'value' => 'string',
                                    'name' => 'label',
                                    'xsi:type' => 'string',
                                    'translate' => 'false',
                                ],
                                'class' => [
                                    'value' => 'string',
                                    'name' => 'class',
                                    'xsi:type' => 'string',
                                ],
                                'url' => [
                                    'name' => 'url',
                                    'xsi:type' => 'url',
                                    'param' => [
                                        'string' => [
                                            'name' => 'string',
                                            'value' => 'string',
                                        ],
                                    ],
                                    'path' => 'string',
                                ],
                                'name' => [
                                    'name' => 'name',
                                    'value' => 'string',
                                    'xsi:type' => 'string',
                                ],
                            ],
                        ],
                        'string1' => [
                            'name' => 'string1',
                            'xsi:type' => 'array',
                            'item' => [
                                'label' => [
                                    'value' => 'string',
                                    'name' => 'label',
                                    'xsi:type' => 'string',
                                    'translate' => 'false',
                                ],
                                'class' => [
                                    'value' => 'string',
                                    'name' => 'class',
                                    'xsi:type' => 'string',
                                ],
                                'url' => [
                                    'name' => 'url',
                                    'xsi:type' => 'url',
                                    'param' => [
                                        'string' => [
                                            'name' => 'string',
                                            'value' => 'string',
                                        ],
                                    ],
                                    'path' => 'string',
                                ],
                                'name' => [
                                    'name' => 'name',
                                    'value' => 'string1',
                                    'xsi:type' => 'string',
                                ],
                            ],
                        ],
                    ],
                ],
                'spinner' => [
                    'name' => 'spinner',
                    'xsi:type' => 'string',
                    'value' => 'string',
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
    ],
    'children' => [],
    'uiComponentType' => 'form',
];
