<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
