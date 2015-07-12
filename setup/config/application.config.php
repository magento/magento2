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
            __DIR__ . '/../src',
        ],
        'config_glob_paths' => [
            __DIR__ . '/autoload/{,*.}{global,local}.php',
        ],
    ],
    'listeners' => ['Magento\Setup\Mvc\Bootstrap\InitParamListener'],
    'service_manager' => [
        'factories' => [
            InitParamListener::BOOTSTRAP_PARAM => 'Magento\Setup\Mvc\Bootstrap\InitParamListener',
        ],
    ],
];
