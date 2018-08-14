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
                        'preserveSelectionsOnFilter' => [
                            'name' => 'preserveSelectionsOnFilter',
                            'xsi:type' => 'boolean',
                            'value' => 'false',
                        ],
                        'indexField' => [
                            'name' => 'indexField',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'fieldAction' => [
                            'name' => 'fieldAction',
                            'xsi:type' => 'array',
                            'item' => [
                                'provider' => [
                                    'name' => 'provider',
                                    'value' => 'string',
                                    'xsi:type' => 'string',
                                ],
                                'target' => [
                                    'name' => 'target',
                                    'value' => 'string',
                                    'xsi:type' => 'string',
                                ],
                                'params' => [
                                    'name' => 'params',
                                    'xsi:type' => 'array',
                                    'item' => [
                                        0 => [
                                            'name' => 0,
                                            'xsi:type' => 'boolean',
                                            'value' => 'true',
                                            'active' => 'false'
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'add_field' => [
                            'name' => 'add_field',
                            'xsi:type' => 'boolean',
                            'value' => 'false',
                        ],
                        'draggable' => [
                            'name' => 'draggable',
                            'xsi:type' => 'boolean',
                            'value' => 'false',
                        ],
                        'sorting' => [
                            'name' => 'sorting',
                            'xsi:type' => 'string',
                            'value' => 'asc',
                        ],
                        'sortable' => [
                            'name' => 'sortable',
                            'xsi:type' => 'boolean',
                            'value' => 'false',
                        ],
                        'controlVisibility' => [
                            'name' => 'controlVisibility',
                            'xsi:type' => 'boolean',
                            'value' => 'false',
                        ],
                        'bodyTmpl' => [
                            'name' => 'bodyTmpl',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'headerTmpl' => [
                            'name' => 'headerTmpl',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'label' => [
                            'name' => 'label',
                            'translate' => 'true',
                            'xsi:type' => 'string',
                        ],
                        'fieldClass' => [
                            'name' => 'fieldClass',
                            'xsi:type' => 'array',
                            'item' => [
                                'string' => [
                                    'name' => 'string',
                                    'xsi:type' => 'boolean',
                                    'value' => 'false',
                                ],
                            ],
                        ],
                        'disableAction' => [
                            'name' => 'disableAction',
                            'xsi:type' => 'boolean',
                            'value' => 'false',
                        ],
                        'filter' => [
                            'name' => 'filter',
                            'xsi:type' => 'string',
                            'value' => 'true',
                        ],
                        'dataType' => [
                            'name' => 'dataType',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'visible' => [
                            'name' => 'visible',
                            'xsi:type' => 'boolean',
                            'value' => 'false',
                        ],
                        'resizeEnabled' => [
                            'name' => 'resizeEnabled',
                            'xsi:type' => 'boolean',
                            'value' => 'false',
                        ],
                        'has_preview' => [
                            'name' => 'has_preview',
                            'xsi:type' => 'boolean',
                            'value' => 'false',
                        ],
                        'altField' => [
                            'name' => 'altField',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'resizeDefaultWidth' => [
                            'name' => 'resizeDefaultWidth',
                            'xsi:type' => 'number',
                            'value' => '0',
                        ],
                        'dateFormat' => [
                            'name' => 'dateFormat',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'timeFormat' => [
                            'name' => 'timeFormat',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'timezone' => [
                            'name' => 'timezone',
                            'xsi:type' => 'string',
                            'value' => 'false',
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
                'options' => [
                    'name' => 'options',
                    'xsi:type' => 'array',
                    'item' => [
                        'anySimpleType' => [
                            'xsi:type' => 'boolean',
                            'active' => 'false',
                            'name' => 'anySimpleType',
                            'value' => 'true',
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
    'uiComponentType' => 'selectionsColumn',
];
