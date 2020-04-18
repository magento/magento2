<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    'top01' => [
        'topic' => 'top01',
        'disabled' => false,
        'connection' => [],
    ],
    'top02' => [
        'topic' => 'top02',
        'disabled' => false,
        'connection' => [],
    ],
    'top03' => [
        'topic' => 'top03',
        'disabled' => true,
        'connection' => [],
    ],
    'top04' => [
        'topic' => 'top04',
        'disabled' => false,
        'connection' => ['name' => 'amqp', 'exchange' => 'magento', 'disabled' => false],
    ],
    'top05' => [
        'topic' => 'top05',
        'disabled' => false,
        'connection' => ['name' => 'amqp', 'exchange' => 'exch01', 'disabled' => false],
    ],
    'top06' => [
        'topic' => 'top06',
        'disabled' => true,
        'connection' => ['name' => 'amqp', 'exchange' => 'exch01', 'disabled' => false],
    ],
];
