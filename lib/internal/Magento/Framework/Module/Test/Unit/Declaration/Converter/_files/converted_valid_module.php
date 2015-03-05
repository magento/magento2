<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'Module_One' => [
        'name' => 'Module_One',
        'schema_version' => '1.0.0.0',
        'sequence' => [],
    ],
    'Module_Two' => [
        'name' => 'Module_Two',
        'schema_version' => '2.0.0.0',
        'sequence' => ['Module_One'],
    ]
];
