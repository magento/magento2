<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    [
        'section' => 'dev',
        'groups' => [
            'log' => [
                'fields' => [
                    'active' => ['value' => '1'],
                    'file' => ['value' => 'fileName.log'],
                    'exception_file' => ['value' => 'exceptionFileName.log'],
                ],
            ],
            'debug' => [
                'fields' => [
                    'template_hints_storefront' => ['value' => '1'],
                    'template_hints_blocks' => ['value' => '0'],
                ],
            ],
        ],
        'expected' => [
            'dev/log' => [
                'dev/log/active' => '1',
                'dev/log/file' => 'fileName.log',
                'dev/log/exception_file' => 'exceptionFileName.log',
            ],
            'dev/debug' => [
                'dev/debug/template_hints_storefront' => '1',
                'dev/debug/template_hints_blocks' => '0',
            ],
        ],
    ]
];
