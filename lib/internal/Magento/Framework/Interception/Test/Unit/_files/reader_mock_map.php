<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item;
use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item\Enhanced;
use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemContainer;
use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemContainerPlugin\Simple;
use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemPlugin\Advanced;
use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemPlugin\Simple as ItemPluginSimple;
use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\StartingBackslash;
use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\StartingBackslash\Plugin;

return [
    [
        'primary',
        [
            Item::class => [
                'plugins' => [
                    'primary_plugin' => [
                        'sortOrder' => 1,
                        'instance' => ItemPluginSimple::class,
                    ],
                ],
            ]
        ],
    ],
    [
        'global',
        [
            Item::class => [
                'plugins' => [
                    'simple_plugin' => [
                        'sortOrder' => 10,
                        'instance' => ItemPluginSimple::class,
                    ],
                ],
            ]
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
            ItemContainer::class => [
                'plugins' => [
                    'simple_plugin' => [
                        'sortOrder' => 15,
                        'instance' => Simple::class,
                    ],
                ],
            ],
            StartingBackslash::class => [
                'plugins' => [
                    'simple_plugin' => [
                        'sortOrder' => 20,
                        'instance' => Plugin::class,
                    ],
                ],
            ]
        ]
    ],
    [
        'frontend',
        [
            Item::class => [
                'plugins' => [
                    'simple_plugin' => [
                        'disabled' => true
                    ]
                ],
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
            ]
        ]
    ],
    [
        'emptyscope',
        [

        ]
    ]
];
