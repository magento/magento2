<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    'root' => [
        'node_one' => [
            'attributeOne' => '10',
            'attributeTwo' => '20',
            'subnode' => [
                ['attributeThree' => '30'],
                ['attributeThree' => '40', 'attributeFour' => '40', 'value' => 'Value1'],
                ['attributeThree' => '50', 'value' => 'value_from_new_line'],
                ['attributeThree' => '60', 'value' => 'auto_formatted_by_ide_value_due_to_line_size_restriction']
            ],
            'books' => ['attributeFive' => '50'],
        ],
        'multipleNode' => [
            'one' => ['id' => 'one', 'name' => 'name1', 'value' => '1'],
            'two' => ['id' => 'two', 'name' => 'name2', 'value' => '2'],
            'three' => ['id' => 'three', 'name' => 'name3', 'value' => 'value_from_new_line'],
            'four' => [
                'id' => 'four',
                'name' => 'name4',
                'value' => 'auto_formatted_by_ide_value_due_to_line_size_restriction'
            ],
        ],
        'someOtherVal' => '',
        'someDataVal' => '',
        'valueFromNewLine' => 'value_from_new_line',
        'autoFormattedValue' => 'auto_formatted_by_ide_value_due_to_line_size_restriction'
    ]
];
