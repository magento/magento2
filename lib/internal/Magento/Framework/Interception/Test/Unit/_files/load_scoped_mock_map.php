<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item;
use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemContainer;
use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemContainerPlugin\Simple;
use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemPlugin\Advanced;
use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemPlugin\Simple as ItemPluginSimple;
use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\StartingBackslash;
use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\StartingBackslash\Plugin;

return [
    [
        [1 => 'global'],
        [],
        [],
        [],
        null,
        [
            [],
            [1 => 'global'],
            ['global' => true],
            [
                Item::class => [
                    'simple_plugin' => [
                        'sortOrder' => 10,
                        'instance' => ItemPluginSimple::class,
                    ],
                ]
            ],
            [],
            []
        ],
    ],
    [
        [
            'global',
            'backend'
        ],
        [],
        [],
        [],
        null,
        [
            [],
            [
                'global',
                'backend'
            ],
            [
                'global' => true,
                'backend' => true
            ],
            [
                Item::class => [
                    'simple_plugin' => [
                        'sortOrder' => 10,
                        'instance' => Simple::class,
                    ],
                    'advanced_plugin' => [
                        'sortOrder' => 5,
                        'instance' => Advanced::class,
                    ],
                ],
                ItemContainer::class => [
                    'simple_plugin' => [
                        'sortOrder' => 15,
                        'instance' => Simple::class,
                    ],
                ],
                StartingBackslash::class => [
                    'simple_plugin' => [
                        'sortOrder' => 20,
                        'instance' => Plugin::class,
                    ],
                ]
            ],
            [],
            []
        ]
    ]
];
