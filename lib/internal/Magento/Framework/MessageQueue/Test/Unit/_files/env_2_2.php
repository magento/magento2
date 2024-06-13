<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

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
