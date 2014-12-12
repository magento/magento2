<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
return [
    'root' => [
        [
            'node_one' => [
                [
                    '__attributes__' => ['attributeOne' => '10', 'attributeTwo' => '20'],
                    'subnode' => [
                        ['__attributes__' => ['attributeThree' => '30'], '__content__' => 'Value1'],
                        ['__attributes__' => ['attributeFour' => '40']],
                    ],
                    'books' => [['__attributes__' => ['attributeFive' => '50']]],
                ],
            ],
        ],
    ]
];
