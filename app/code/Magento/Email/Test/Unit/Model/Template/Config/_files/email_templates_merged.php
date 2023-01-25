<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

return [
    'template_one' => [
        'label' => 'Template One',
        'file' => 'one.html',
        'type' => 'html',
        'module' => 'Fixture_ModuleOne',
        'area' => 'frontend',
    ],
    'template_two' => [
        'label' => 'Template 2',
        'file' => '2.txt',
        'type' => 'text',
        'module' => 'Fixture_ModuleTwo',
        'area' => 'adminhtml',
    ]
];
