<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Setup\Di\MagentoDiFactory;
use Magento\Setup\Mvc\Bootstrap\InitParamListener;

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
            InitParamListener::BOOTSTRAP_PARAM => InitParamListener::class,
            \Magento\Framework\App\MaintenanceMode::class => MagentoDiFactory::class,
            \Magento\Setup\Model\ConfigGenerator::class => MagentoDiFactory::class,
            \Magento\Indexer\Console\Command\IndexerReindexCommand::class => MagentoDiFactory::class,
            \Symfony\Component\Console\Helper\TableFactory::class => MagentoDiFactory::class,
            \Magento\Deploy\Console\InputValidator::class => MagentoDiFactory::class,
            \Magento\Framework\App\State::class => MagentoDiFactory::class,
        ],
    ]
];
