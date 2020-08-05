<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Laminas\EventManager\EventManagerInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\ServiceManager\ServiceManager;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\DB\Logger\Quiet;
use Magento\Framework\DB\LoggerInterface;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Locale\Config;
use Magento\Framework\Locale\ConfigInterface;
use Magento\Framework\Setup\Declaration\Schema\SchemaConfig;

return [
    'di' => [
        'instance' => [
            'preference' => [
                EventManagerInterface::class => 'EventManager',
                ServiceLocatorInterface::class => ServiceManager::class,
                LoggerInterface::class => Quiet::class,
                ConfigInterface::class => Config::class,
                DriverInterface::class => \Magento\Framework\Filesystem\Driver\File::class,
                ComponentRegistrarInterface::class => ComponentRegistrar::class,
            ],
            SchemaConfig::class => [
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
