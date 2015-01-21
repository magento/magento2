<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'root' => [
        [
            'simple' => [['node_two' => [['__content__' => 'valueOne']]]],
            'cdata' => [['node_one' => [['__content__' => '<valueTwo>']]]],
            'mixed' => [
                [
                    'node_one' => [
                        ['__attributes__' => ['attributeOne' => '10'], '__content__' => '<valueThree>'],
                        ['__attributes__' => ['attributeTwo' => '20'], '__content__' => 'valueFour'],
                    ],
                ],
            ],
            'mixed_different_names' => [
                [
                    'node_one' => [['__content__' => 'valueFive']],
                    'node_two' => [['__content__' => 'valueSix']],
                ],
            ],
        ],
    ]
];
