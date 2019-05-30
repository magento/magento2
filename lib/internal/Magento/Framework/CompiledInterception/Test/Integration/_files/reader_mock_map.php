<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\CompiledInterception\Test\Integration\CompiledInterceptor\Custom\Module\Model\ComplexItem;
use Magento\Framework\CompiledInterception\Test\Integration\CompiledInterceptor\Custom\Module\Model\ComplexItemTyped;
use Magento\Framework\CompiledInterception\Test\Integration\CompiledInterceptor\Custom\Module\Model\Item;
use Magento\Framework\CompiledInterception\Test\Integration\CompiledInterceptor\Custom\Module\Model\Item\Enhanced;
use Magento\Framework\CompiledInterception\Test\Integration\CompiledInterceptor\Custom\Module\Model\ItemPlugin\Advanced;
use Magento\Framework\CompiledInterception\Test\Integration\CompiledInterceptor\Custom\Module\Model\ItemPlugin\Complex;
use Magento\Framework\CompiledInterception\Test\Integration\CompiledInterceptor\Custom\Module\Model\ItemPlugin\Simple;
use Magento\Framework\CompiledInterception\Test\Integration\CompiledInterceptor\Custom\Module\Model\SecondItem;

return [
    [
        'global',
        [
            Item::class => [
                'plugins' => [
                    'simple_plugin' => [
                        'sortOrder' => 10,
                        'instance' => Simple::class,
                    ],
                ],
            ],
            ComplexItem::class => [
                'plugins' => [
                    'advanced_plugin' => [
                        'sortOrder' => 5,
                        'instance' => Advanced::class,
                    ],
                ],
            ],
        ],
    ],
    [
        'backend',
        [
            Item::class => [
                'plugins' => [
                    'advanced_plugin' => [
                        'sortOrder' => 5,
                        'instance' => Advanced::class,
                    ],
                ],
            ],
            ComplexItem::class => [
                'plugins' => [
                    'complex_plugin' => [
                        'sortOrder' => 15,
                        'instance' => Complex::class,
                    ],
                    'advanced_plugin' => [
                        'sortOrder' => 5,
                        'instance' => Advanced::class,
                    ],
                ],
            ],
            ComplexItemTyped::class => [
                'plugins' => [
                    'complex_plugin' => [
                        'sortOrder' => 25,
                        'instance' => Complex::class,
                    ],
                    'advanced_plugin' => [
                        'sortOrder' => 5,
                        'instance' => Advanced::class,
                    ],
                ],
            ],
        ]
    ],
    [
        'frontend',
        [
            Item::class => [
                'plugins' => ['simple_plugin' => ['disabled' => true]],
            ],
            Enhanced::class => [
                'plugins' => [
                    'advanced_plugin' => [
                        'sortOrder' => 5,
                        'instance' => Advanced::class,
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
            ],
            SecondItem::class => [
                'plugins' => [
                    'simple_plugin1' => [
                        'sortOrder' => 5,
                        'instance' => Simple::class,
                    ],
                    'advanced_plugin1' => [
                        'sortOrder' => 5,
                        'instance' => Advanced::class,
                    ],
                    'advanced_plugin2' => [
                        'sortOrder' => 10,
                        'instance' => Advanced::class,
                    ],
                    'simple_plugin2' => [
                        'sortOrder' => 11,
                        'instance' => Simple::class,
                    ],
                    'simple_plugin3' => [
                        'sortOrder' => 12,
                        'instance' => Simple::class,
                    ],
                    'advanced_plugin3' => [
                        'sortOrder' => 15,
                        'instance' => Advanced::class,
                    ],
                    'advanced_plugin4' => [
                        'sortOrder' => 25,
                        'instance' => Advanced::class,
                    ],
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
