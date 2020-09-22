<?php declare(strict_types=1);

use Magento\Framework\MessageQueue\ConsumerInterface;

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'consumer1' => [
        'name' => 'consumer1',
        'queue' => 'queue1',
        'consumerInstance' => 'consumerClass1',
        'handlers' => [
            0 => [
                'type' => 'handlerClassOne',
                'method' => 'handlerMethodOne'
            ],
        ],
        'connection' => 'connection1',
        'maxMessages' => '200',
        'maxIdleTime' => '500',
        'sleep' => '5',
        'onlySpawnWhenMessageAvailable' => true
    ],
    'consumer2' => [
        'name' => 'consumer2',
        'queue' => 'queue2',
        'consumerInstance' => 'consumerClass2',
        'handlers' => [
            0 => [
                'type' => 'handlerClassTwo',
                'method' => 'handlerMethodTwo'
            ],
        ],
        'connection' => 'connection2',
        'maxMessages' => '100',
        'maxIdleTime' => '1000',
        'sleep' => '2',
        'onlySpawnWhenMessageAvailable' => false
    ],
    'consumer3' => [
        'name' => 'consumer3',
        'queue' => 'queue3',
        'consumerInstance' => 'consumerClass3',
        'handlers' => [
            0 => [
                'type' => 'handlerClassThree',
                'method' => 'handlerMethodThree'
            ],
        ],
        'connection' => 'connection3',
        'maxMessages' => '50',
        'maxIdleTime' => '100',
        'sleep' => null,
        'onlySpawnWhenMessageAvailable' => false
    ],
    'consumer4' => [
        'name' => 'consumer4',
        'queue' => 'queue4',
        'consumerInstance' => 'consumerClass4',

        'handlers' => [
            0 => [
                'type' => 'handlerClassFour',
                'method' => 'handlerMethodFour'
            ],
        ],
        'connection' => 'connection4',
        'maxMessages' => '10',
        'maxIdleTime' => null,
        'sleep' => null,
        'onlySpawnWhenMessageAvailable' => false
    ],
    'consumer5' => [
        'name' => 'consumer5',
        'queue' => 'queue5',
        'consumerInstance' => 'consumerClass5',
        'handlers' => [
            0 => [
                'type' => 'handlerClassFive',
                'method' => 'handlerMethodFive'
            ],
        ],
        'connection' => 'connection5',
        'maxMessages' => null,
        'maxIdleTime' => null,
        'sleep' => null,
        'onlySpawnWhenMessageAvailable' => false
    ],
    'consumer6' => [
        'name' => 'consumer6',
        'queue' => 'queue6',
        'consumerInstance' => 'consumerClass6',
        'handlers' => [
            0 => [
                'type' => 'handlerClassSix',
                'method' => 'handlerMethodSix'
            ],
        ],
        'connection' => 'amqp',
        'maxMessages' => null,
        'maxIdleTime' => null,
        'sleep' => null,
        'onlySpawnWhenMessageAvailable' => false
    ],
    'consumer7' => [
        'name' => 'consumer7',
        'queue' => 'queue7',
        'consumerInstance' => ConsumerInterface::class,
        'handlers' => [
            0 => [
                'type' => 'handlerClassSeven',
                'method' => 'handlerMethodSeven'
            ],
        ],
        'connection' => 'amqp',
        'maxMessages' => null,
        'maxIdleTime' => null,
        'sleep' => null,
        'onlySpawnWhenMessageAvailable' => false
    ],
    'consumer8' => [
        'name' => 'consumer8',
        'queue' => 'queue8',
        'consumerInstance' => ConsumerInterface::class,
        'handlers' => [],
        'connection' => 'amqp',
        'maxMessages' => null,
        'maxIdleTime' => null,
        'sleep' => null,
        'onlySpawnWhenMessageAvailable' => false
    ],
];
