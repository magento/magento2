<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Setup\Mvc\Bootstrap\InitParamListener;
use Laminas\Di\ConfigInterface;
use Laminas\Di\InjectorInterface;
use Laminas\Di\Container\ConfigFactory;
use Laminas\Di\Container\InjectorFactory;

return [
    'modules' => require __DIR__ . '/modules.config.php',
    'module_listener_options' => [
        'module_paths' => [
            __DIR__ . '/../src',
        ],
        'config_glob_paths' => [
            __DIR__ . '/autoload/{,*.}{global,local}.php',
        ],
    ],
    'listeners' => [
        InitParamListener::class
    ],
    'service_manager' => [
        'factories' => [
//            ConfigInterface::class => ConfigFactory::class,
//            InjectorInterface::class => InjectorFactory::class,
            InitParamListener::BOOTSTRAP_PARAM => InitParamListener::class
        ],
    ]
];
