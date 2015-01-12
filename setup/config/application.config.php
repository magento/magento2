<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Setup\Mvc\Bootstrap\InitParamListener;

return [
    'modules' => [
        'Magento\Setup',
    ],
    'module_listener_options' => [
        'module_paths' => [
            __DIR__ . '/../module',
            __DIR__ . '/../vendor',
        ],
        'config_glob_paths' => [
            __DIR__ . '/autoload/{,*.}{global,local}.php',
        ],
    ],
    'listeners' => ['Magento\Setup\Mvc\Bootstrap\InitParamListener', 'Magento\Setup\Mvc\Console\RouteListener'],
    'service_manager' => [
        'factories' => [
            InitParamListener::BOOTSTRAP_PARAM => 'Magento\Setup\Mvc\Bootstrap\InitParamListener',
        ],
    ],
];
