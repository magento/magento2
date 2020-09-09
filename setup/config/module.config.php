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
        'strategies' => ['ViewJsonStrategy'],
    ],
    'translator' => [
        'translation_file_patterns' => [
            [
                'type' => 'gettext',
                'base_dir' => __DIR__ . '/../lang',
                'pattern' => '%s.mo',
            ],
        ],
    ],
    'service_manager' => [
        'aliases' => [
            'translator' => 'MvcTranslator'
        ]
    ],
    'controllers' => [
        'abstract_factories' => [
            \Zend\Mvc\Controller\LazyControllerAbstractFactory::class,
        ],
    ],
];
