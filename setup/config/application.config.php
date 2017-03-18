<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Setup\Mvc\Bootstrap\InitParamListener;
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
    'listeners' => [\Magento\Setup\Mvc\Bootstrap\InitParamListener::class],
    'service_manager' => [
        'invokables' => [
            \Magento\Setup\Mvc\Bootstrap\InitParamListener::class => \Magento\Setup\Mvc\Bootstrap\InitParamListener::class
        ],
        'factories' => [
            \Magento\Setup\Mvc\Bootstrap\InitParamListener::BOOTSTRAP_PARAM => \Magento\Setup\Mvc\Bootstrap\InitParamListener::class,
            DiAbstractServiceFactory::class => \Zend\Mvc\Service\DiAbstractServiceFactoryFactory::class
        ],
    ],
];
