<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

return [
    'some.key' => 'some.val',
    'group.1' => [
        'fields' => [
            'f1.1' => ['value' => 'f1.1.val'],
            'f1.2' => ['value' => 'f1.2.val'],
            'g1.1' => ['value' => 'g1.1.val'],
        ],
    ],
    'group.2' => [
        'fields' => ['f2.1' => ['value' => 'f2.1.val'], 'f2.2' => ['value' => 'f2.2.val']],
        'groups' => [
            'group.2.1' => [
                'fields' => [
                    'f2.1.1' => ['value' => 'f2.1.1.val'],
                    'f2.1.2' => ['value' => 'f2.1.2.val'],
                ],
                'groups' => [
                    'group.2.1.1' => [
                        'fields' => [
                            'f2.1.1.1' => ['value' => 'f2.1.1.1.val'],
                            'f2.1.1.2' => ['value' => 'f2.1.1.2.val'],
                        ],
                    ],
                ],
            ],
        ],
    ]
];
