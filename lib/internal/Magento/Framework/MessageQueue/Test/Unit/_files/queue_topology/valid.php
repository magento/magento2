<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

return [
    'ex01--amqp' => [
        'name' => 'ex01',
        'type' => 'topic',
        'connection' => 'amqp',
        'durable' => true,
        'autoDelete' => false,
        'internal' => false,
        'bindings' => [],
        'arguments' => [],
    ],
    'ex02--amqp' => [
        'name' => 'ex02',
        'type' => 'topic',
        'connection' => 'amqp',
        'durable' => true,
        'autoDelete' => false,
        'internal' => false,
        'bindings' => [],
        'arguments' => [],
    ],
    'ex03--amqp' => [
        'name' => 'ex03',
        'type' => 'topic',
        'connection' => 'amqp',
        'durable' => false,
        'autoDelete' => true,
        'internal' => true,
        'bindings' => [],
        'arguments' => [
            'arg1' => '10',
        ],
    ],
    'ex04--amqp' => [
        'name' => 'ex04',
        'type' => 'topic',
        'connection' => 'amqp',
        'durable' => true,
        'autoDelete' => false,
        'internal' => false,
        'bindings' => [
            'bind01' => [
                'id' => 'bind01',
                'destinationType' => 'queue',
                'destination' => 'queue01',
                'disabled' => true,
                'topic' => 'top01',
                'arguments' => []
            ],
            'bind02' => [
                'id' => 'bind02',
                'destinationType' => 'queue',
                'destination' => 'queue01',
                'disabled' => false,
                'topic' => 'top01',
                'arguments' => [
                    'arg01' => 10
                ]
            ],
        ],
        'arguments' => [],
    ],
];
