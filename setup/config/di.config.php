<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    'di' => [
        'instance' => [
            'preference' => [
                \Zend\EventManager\EventManagerInterface::class => 'EventManager',
                \Zend\ServiceManager\ServiceLocatorInterface::class => \Zend\ServiceManager\ServiceManager::class,
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
