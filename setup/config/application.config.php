<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Setup\Mvc\Bootstrap\InitParamListener;
use Zend\Mvc\Service\DiAbstractServiceFactoryFactory;
use Zend\ServiceManager\Di\DiAbstractServiceFactory;

return [
    'modules' => [
        'Magento\Setup',
    ],
    'module_listener_options' => [
        'module_paths' => [
            __DIR__ . '/../src',
        ],
        'config_glob_paths' => [
            __DIR__ . '/autoload/{,*.}{global,local}.php',
        ],
    ],
    'listeners' => [InitParamListener::class],
    'service_manager' => [
        'invokables' => [
            InitParamListener::class => InitParamListener::class
        ],
        'factories' => [
            InitParamListener::BOOTSTRAP_PARAM => InitParamListener::class,
            DiAbstractServiceFactory::class => DiAbstractServiceFactoryFactory::class
        ],
    ],
];
