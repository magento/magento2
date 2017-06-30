<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    'group.1' => [
        'fields' => ['f1.1' => ['value' => 'f1.1.val'], 'f1.2' => ['value' => 'f1.2.val']],
    ],
    'group.2' => [
        'fields' => [
            'f2.1' => ['value' => 'f2.1.val'],
            'f2.2' => ['value' => 'f2.2.val'],
            'f2.3' => ['value' => ''],
        ],
        'groups' => [
            'group.2.1' => [
                'fields' => [
                    'f2.1.1' => ['value' => 'f2.1.1.val'],
                    'f2.1.2' => ['value' => 'f2.1.2.val'],
                    'f2.1.3' => ['value' => ''],
                ],
                'groups' => [
                    'group.2.1.1' => [
                        'fields' => [
                            'f2.1.1.1' => ['value' => 'f2.1.1.1.val'],
                            'f2.1.1.2' => ['value' => 'f2.1.1.2.val'],
                            'f2.1.1.3' => ['value' => ''],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'group.3' => 'some.data',
];
