<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    'di' => [
        'instance' => [
            'preference' => [
                \Laminas\EventManager\EventManagerInterface::class => 'EventManager',
                \Laminas\ServiceManager\ServiceLocatorInterface::class => \Laminas\ServiceManager\ServiceManager::class,
                \Magento\Framework\DB\LoggerInterface::class => \Magento\Framework\DB\Logger\Quiet::class,
                \Magento\Framework\Locale\ConfigInterface::class => \Magento\Framework\Locale\Config::class,
                \Magento\Framework\Filesystem\DriverInterface::class =>
                    \Magento\Framework\Filesystem\Driver\File::class,
                \Magento\Framework\Component\ComponentRegistrarInterface::class =>
                    \Magento\Framework\Component\ComponentRegistrar::class,
            ],
            \Magento\Framework\Setup\Declaration\Schema\SchemaConfig::class => [
                'parameters' => [
                    'connectionScopes' => [
                        'default',
                        'checkout',
                        'sales'
                    ]
                ]
            ],
        ],
    ],
];
