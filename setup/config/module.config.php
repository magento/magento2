<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    'view_manager' => [
        'display_not_found_reason' => false,
        'display_exceptions'       => false,
        'doctype'                  => 'HTML5',
        'template_path_stack' => [
            'setup' => __DIR__ . '/../view',
        ],
    ],
    'controllers' => [
        'factories' => [
            \Magento\Setup\Controller\Index::class => \Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory::class,
        ],
    ],
];
