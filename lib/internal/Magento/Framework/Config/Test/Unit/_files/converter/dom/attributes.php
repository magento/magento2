<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'root' => [
        [
            'item' => [
                [
                    '__attributes__' => [
                        'id' => 'id1',
                        'attrZero' => 'value 0',
                    ],
                    '__content__' => 'Item 1.1',
                ],
                [
                    '__attributes__' => [
                        'id' => 'id2',
                        'attrOne' => 'value 2',
                    ],
                    'subitem' => [
                        [
                            '__attributes__' => [
                                'id' => 'id2.1',
                                'attrTwo' => 'value 2.1',
                            ],
                            '__content__' => 'Item 2.1',
                        ],
                        [
                            '__attributes__' => [
                                'id' => 'id2.2',
                            ],
                            'value' => [
                                ['__content__' => 1],
                                ['__content__' => 2],
                                ['__content__' => 'test'],
                            ],
                        ],
                    ],
                ],
                [
                    '__attributes__' => [
                        'id' => 'id3',
                        'attrThree' => 'value 3',
                    ],
                    '__content__' => 'Item 3.1',
                ],
            ],
        ],
    ],
];
