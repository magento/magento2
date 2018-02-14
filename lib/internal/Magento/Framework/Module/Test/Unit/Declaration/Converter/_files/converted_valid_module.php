<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'Module_One' => [
        'name' => 'Module_One',
        'setup_version' => null,
        'sequence' => [],
    ],
    'Module_Two' => [
        'name' => 'Module_Two',
        'setup_version' => null,
        'sequence' => ['Module_One'],
    ]
];
