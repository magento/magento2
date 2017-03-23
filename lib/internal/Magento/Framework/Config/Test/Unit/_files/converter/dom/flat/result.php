<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
            ],
            'books' => ['attributeFive' => '50'],
        ],
        'multipleNode' => [
            'one' => ['id' => 'one', 'name' => 'name1', 'value' => '1'],
            'two' => ['id' => 'two', 'name' => 'name2', 'value' => '2'],
        ],
        'someOtherVal' => '',
        'someDataVal' => '',
    ]
];
