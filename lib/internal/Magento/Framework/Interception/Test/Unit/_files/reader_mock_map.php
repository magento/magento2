<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemContainerPlugin\Simple;
use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\StartingBackslash\Plugin;

return [
    [
        'global',
        [
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item::class => [
                'plugins' => [
                    'simple_plugin' => [
                        'sortOrder' => 10,
                        'instance' =>
                            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemPlugin\Simple::class,
                    ],
                ],
            ]
        ],
    ],
    [
        'backend',
        [
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item::class => [
                'plugins' => [
                    'advanced_plugin' => [
                        'sortOrder' => 5,
                        'instance' =>
                            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemPlugin\Advanced::class,
                    ],
                ],
            ],
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemContainer::class => [
                'plugins' => [
                    'simple_plugin' => [
                        'sortOrder' => 15,
                        'instance' => Simple::class,
                    ],
                ],
            ],
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\StartingBackslash::class => [
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
        [\Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item::class => [
                'plugins' => ['simple_plugin' => ['disabled' => true]],
            ], \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item\Enhanced::class => [
                'plugins' => [
                    'advanced_plugin' => [
                        'sortOrder' => 5,
                        'instance' =>
                            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemPlugin\Advanced::class,
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
