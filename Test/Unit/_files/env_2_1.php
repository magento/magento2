<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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
