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
                        'autoRender' => [
                            'name' => 'autoRender',
                            'xsi:type' => 'boolean',
                            'value' => 'false',
                        ],
                        'behaviourType' => [
                            'name' => 'behaviourType',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'externalFilterMode' => [
                            'name' => 'externalFilterMode',
                            'xsi:type' => 'boolean',
                            'value' => 'false',
                        ],
                        'label' => [
                            'name' => 'label',
                            'translate' => 'true',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'editorProvider' => [
                            'name' => 'editorProvider',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'externalCondition' => [
                            'name' => 'externalCondition',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'render_url' => [
                            'name' => 'render_url',
                            'xsi:type' => 'url',
                            'param' => [
                                'string' => [
                                    'name' => 'string',
                                    'value' => 'string',
                                ],
                            ],
                            'path' => 'anySimpleType',
                        ],
                        'update_url' => [
                            'name' => 'update_url',
                            'xsi:type' => 'url',
                            'param' => [
                                'string' => [
                                    'name' => 'string',
                                    'value' => 'string',
                                ],
                            ],
                            'path' => 'anySimpleType',
                        ],
                        'selectionsProvider' => [
                            'name' => 'selectionsProvider',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'externalProvider' => [
                            'name' => 'externalProvider',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'loading' => [
                            'name' => 'loading',
                            'xsi:type' => 'boolean',
                            'value' => 'false',
                        ],
                        'realTimeLink' => [
                            'name' => 'realTimeLink',
                            'xsi:type' => 'boolean',
                            'value' => 'false',
                        ],
                        'externalData' => [
                            'name' => 'externalData',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'dataLinks' => [
                            'name' => 'dataLinks',
                            'xsi:type' => 'array',
                            'item' => [
                                'imports' => [
                                    'name' => 'imports',
                                    'xsi:type' => 'boolean',
                                    'value' => 'false',
                                ],
                                'exports' => [
                                    'name' => 'exports',
                                    'xsi:type' => 'boolean',
                                    'value' => 'false',
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
    'uiComponentType' => 'insertListing',
];
