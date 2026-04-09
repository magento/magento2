<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

return [
    'Module_One' => [
        'name' => 'Module_One',
        'setup_version' => null,
        'sequence' => [],
    ],
    'Module_OneAndHalf' => [
        'name' => 'Module_OneAndHalf',
        'setup_version' => '2.0',
        'sequence' => [],
    ],
    'Module_Two' => [
        'name' => 'Module_Two',
        'setup_version' => null,
        'sequence' => ['Module_One'],
    ]
];
