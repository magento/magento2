<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
