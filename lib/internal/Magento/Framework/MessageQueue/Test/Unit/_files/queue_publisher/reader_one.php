<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

return [
    'top04' => [
        'topic' => 'top04',
        'disabled' => false,
        'connections' => ['amqp' => ['name' => 'amqp', 'exchange' => 'magento8', 'disabled' => false]]
    ],
    'top06' => [
        'topic' => 'top06',
        'disabled' => false,
    ],
];
