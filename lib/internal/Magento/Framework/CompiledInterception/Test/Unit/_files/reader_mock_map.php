<?php

return [
    [
        'global',
        [
            \Magento\Framework\CompiledInterception\Test\Unit\Custom\Module\Model\Item::class => [
                'plugins' => [
                    'simple_plugin' => [
                        'sortOrder' => 10,
                        'instance' =>
                            \Magento\Framework\CompiledInterception\Test\Unit\Custom\Module\Model\ItemPlugin\Simple::class,
                    ],
                ],
            ],
            \Magento\Framework\CompiledInterception\Test\Unit\Custom\Module\Model\ComplexItem::class => [
                'plugins' => [
                    'advanced_plugin' => [
                        'sortOrder' => 5,
                        'instance' =>
                            \Magento\Framework\CompiledInterception\Test\Unit\Custom\Module\Model\ItemPlugin\Advanced::class,
                    ],
                ],
            ],
        ],
    ],
    [
        'backend',
        [
            \Magento\Framework\CompiledInterception\Test\Unit\Custom\Module\Model\Item::class => [
                'plugins' => [
                    'advanced_plugin' => [
                        'sortOrder' => 5,
                        'instance' =>
                            \Magento\Framework\CompiledInterception\Test\Unit\Custom\Module\Model\ItemPlugin\Advanced::class,
                    ],
                ],
            ],
            \Magento\Framework\CompiledInterception\Test\Unit\Custom\Module\Model\ComplexItem::class => [
                'plugins' => [
                    'complex_plugin' => [
                        'sortOrder' => 15,
                        'instance' =>
                            \Magento\Framework\CompiledInterception\Test\Unit\Custom\Module\Model\ItemPlugin\Complex::class,
                    ],
                    'advanced_plugin' => [
                        'sortOrder' => 5,
                        'instance' =>
                            \Magento\Framework\CompiledInterception\Test\Unit\Custom\Module\Model\ItemPlugin\Advanced::class,
                    ],
                ],
            ],
            \Magento\Framework\CompiledInterception\Test\Unit\Custom\Module\Model\ComplexItemTyped::class => [
                'plugins' => [
                    'complex_plugin' => [
                        'sortOrder' => 25,
                        'instance' =>
                            \Magento\Framework\CompiledInterception\Test\Unit\Custom\Module\Model\ItemPlugin\Complex::class,
                    ],
                    'advanced_plugin' => [
                        'sortOrder' => 5,
                        'instance' =>
                            \Magento\Framework\CompiledInterception\Test\Unit\Custom\Module\Model\ItemPlugin\Advanced::class,
                    ],
                ],
            ],
        ]
    ],
    [
        'frontend',
        [\Magento\Framework\CompiledInterception\Test\Unit\Custom\Module\Model\Item::class => [
                'plugins' => ['simple_plugin' => ['disabled' => true]],
            ], \Magento\Framework\CompiledInterception\Test\Unit\Custom\Module\Model\Item\Enhanced::class => [
                'plugins' => [
                    'advanced_plugin' => [
                        'sortOrder' => 5,
                        'instance' =>
                            \Magento\Framework\CompiledInterception\Test\Unit\Custom\Module\Model\ItemPlugin\Advanced::class,
                    ],
                ],
            ],
            'SomeType' => [
                'plugins' => [
                    'simple_plugin' => [
                        'instance' => 'NonExistingPluginClass',
                    ],
                ],
            ],
            'typeWithoutInstance' => [
                'plugins' => [
                    'simple_plugin' => [],
                ],
            ]
        ]
    ],
    [
        'emptyscope',
        [

        ]
    ]
];
