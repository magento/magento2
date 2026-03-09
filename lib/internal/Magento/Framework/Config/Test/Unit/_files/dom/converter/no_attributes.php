<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

return [
    'root' => [
        [
            'node_one' => [['subnode' => [['__content__' => 'Value1']]]],
            'node_two' => [
                ['subnode' => [['__content__' => 'Value2'], ['__content__' => 'Value3']]],
            ],
            'node_three' => [['__content__' => 'Value4']],
        ],
    ]
];
