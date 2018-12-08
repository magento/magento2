<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
// @codingStandardsIgnoreFile
=======

>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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
<<<<<<< HEAD
            'four' => ['id' => 'four', 'name' => 'name4', 'value' => 'auto_formatted_by_ide_value_due_to_line_size_restriction'],
=======
            'four' => [
                'id' => 'four',
                'name' => 'name4',
                'value' => 'auto_formatted_by_ide_value_due_to_line_size_restriction'
            ],
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
        ],
        'someOtherVal' => '',
        'someDataVal' => '',
        'valueFromNewLine' => 'value_from_new_line',
        'autoFormattedValue' => 'auto_formatted_by_ide_value_due_to_line_size_restriction'
    ]
];
