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
                        'defaultRecord' => [
                            'name' => 'defaultRecord',
                            'xsi:type' => 'boolean',
                            'value' => 'false',
                        ],
                        'columnsHeader' => [
                            'name' => 'columnsHeader',
                            'xsi:type' => 'boolean',
                            'value' => 'false',
                        ],
                        'columnsHeaderClasses' => [
                            'name' => 'columnsHeaderClasses',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'recordTemplate' => [
                            'name' => 'recordTemplate',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'collapsibleHeader' => [
                            'name' => 'collapsibleHeader',
                            'xsi:type' => 'boolean',
                            'value' => 'false',
                        ],
                        'addButtonLabel' => [
                            'name' => 'addButtonLabel',
                            'translate' => 'true',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'addButton' => [
                            'name' => 'addButton',
                            'xsi:type' => 'boolean',
                            'value' => 'false',
                        ],
                        'deleteProperty' => [
                            'name' => 'deleteProperty',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'identificationProperty' => [
                            'name' => 'identificationProperty',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'deleteValue' => [
                            'name' => 'deleteValue',
                            'xsi:type' => 'boolean',
                            'value' => 'false',
                        ],
                        'pageSize' => [
                            'name' => 'pageSize',
                            'xsi:type' => 'number',
                            'value' => '0',
                        ],
                        'currentPage' => [
                            'name' => 'currentPage',
                            'xsi:type' => 'number',
                            'value' => '0',
                        ],
                        'scopeLabel' => [
                            'name' => 'scopeLabel',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'dndConfig' => [
                            'name' => 'dndConfig',
                            'xsi:type' => 'array',
                            'item' => [
                                'anySimpleType' => [
                                    'name' => 'anySimpleType',
                                    'xsi:type' => 'string',
                                    'value' => 'string',
                                    'active' => 'false',
                                ],
                            ],
                        ],
                        'visible' => [
                            'name' => 'visible',
                            'xsi:type' => 'boolean',
                            'value' => 'false',
                        ],
                        'disabled' => [
                            'name' => 'disabled',
                            'xsi:type' => 'boolean',
                            'value' => 'false',
                        ],
                        'labelVisible' => [
                            'name' => 'labelVisible',
                            'xsi:type' => 'boolean',
                            'value' => 'false',
                        ],
                        'showFallbackReset' => [
                            'name' => 'showFallbackReset',
                            'xsi:type' => 'boolean',
                            'value' => 'false',
                        ],
                        'focused' => [
                            'name' => 'focused',
                            'xsi:type' => 'boolean',
                            'value' => 'false',
                        ],
                        'label' => [
                            'name' => 'label',
                            'translate' => 'true',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'dataType' => [
                            'name' => 'dataType',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'elementTmpl' => [
                            'name' => 'elementTmpl',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'tooltipTpl' => [
                            'name' => 'tooltipTpl',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'fallbackResetTpl' => [
                            'name' => 'fallbackResetTpl',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'placeholder' => [
                            'name' => 'placeholder',
                            'xsi:type' => 'string',
                            'value' => 'text',
                            'translate' => 'true',
                        ],
                        'validation' => [
                            'name' => 'validation',
                            'xsi:type' => 'array',
                            'item' => [
                                'anySimpleType' => [
                                    'name' => 'anySimpleType',
                                    'xsi:type' => 'boolean',
                                    'value' => 'true',
                                    'active' => 'false',
                                ],
                            ],
                        ],
                        'notice' => [
                            'name' => 'notice',
                            'translate' => 'true',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'required' => [
                            'name' => 'required',
                            'xsi:type' => 'boolean',
                            'value' => 'false',
                        ],
                        'switcherConfig' => [
                            'name' => 'switcherConfig',
                            'xsi:type' => 'array',
                            'item' => [
                                'name' => [
                                    'name' => 'name',
                                    'xsi:type' => 'string',
                                    'value' => 'string',
                                ],
                                'component' => [
                                    'name' => 'component',
                                    'xsi:type' => 'string',
                                    'value' => 'string',
                                ],
                                'target' => [
                                    'name' => 'target',
                                    'xsi:type' => 'string',
                                    'value' => 'string',
                                ],
                                'property' => [
                                    'name' => 'property',
                                    'xsi:type' => 'string',
                                    'value' => 'string',
                                ],
                                'enabled' => [
                                    'name' => 'enabled',
                                    'xsi:type' => 'boolean',
                                    'value' => 'true',
                                ],
                                'rules' => [
                                    'name' => 'rules',
                                    'xsi:type' => 'array',
                                    'item' => [
                                        'string' => [
                                            'name' => 'string',
                                            'xsi:type' => 'array',
                                            'item' => [
                                                'value' => [
                                                    'name' => 'value',
                                                    'xsi:type' => 'string',
                                                    'value' => 'string',
                                                ],
                                                'actions' => [
                                                    'name' => 'actions',
                                                    'xsi:type' => 'array',
                                                    'item' => [
                                                        'string' => [
                                                            'name' => 'string',
                                                            'xsi:type' => 'array',
                                                            'item' => [
                                                                'target' => [
                                                                    'name' => 'target',
                                                                    'xsi:type' => 'string',
                                                                    'value' => 'string',
                                                                ],
                                                                'callback' => [
                                                                    'name' => 'callback',
                                                                    'xsi:type' => 'string',
                                                                    'value' => 'string',
                                                                ],
                                                                'params' => [
                                                                    'name' => 'params',
                                                                    'xsi:type' => 'array',
                                                                    'item' => [
                                                                        'string' => [
                                                                            'name' => 'string',
                                                                            'active' => 'true',
                                                                            'xsi:type' => 'string',
                                                                        ],
                                                                    ],
                                                                ],
                                                            ],
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'tooltip' => [
                            'name' => 'tooltip',
                            'xsi:type' => 'array',
                            'item' => [
                                'link' => [
                                    'name' => 'link',
                                    'xsi:type' => 'string',
                                    'value' => 'string',
                                ],
                                'description' => [
                                    'name' => 'description',
                                    'translate' => 'true',
                                    'xsi:type' => 'string',
                                    'value' => 'string',
                                ],
                            ],
                        ],
                        'additionalClasses' => [
                            'name' => 'additionalClasses',
                            'xsi:type' => 'array',
                            'item' => [
                                'string' => [
                                    'name' => 'string',
                                    'xsi:type' => 'boolean',
                                    'value' => 'false',
                                ],
                            ],
                        ],
                        'addbefore' => [
                            'name' => 'addbefore',
                            'translate' => 'true',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'addafter' => [
                            'name' => 'addafter',
                            'translate' => 'true',
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
    'uiComponentType' => 'dynamicRows',
];
