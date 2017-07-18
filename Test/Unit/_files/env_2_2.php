<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'config' => [
        'publishers' => [
            'inventory.counter.updated' => [
                'connections' => [
                    'amqp' => [
                        'name' => 'db',
                        'exchange' => 'magento-db'
                    ],
                ]
            ]
        ],
        'consumers' => [
            'inventoryQtyCounter' => [
                'connection' => 'db'
            ]
        ]
    ]
];
