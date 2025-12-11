<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

return [
    'publishers' => [
        'amqp-magento' => [
            'name' => 'amqp-magento',
            'connection' => 'db',
            'exchange' => 'magento-db'
        ],
    ],
    'consumers' => [
        'inventoryQtyCounter' => [
            'connection' => 'db'
        ],
    ]
];
