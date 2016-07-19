<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    'consumer1' => [
        'name' => 'consumer1',
        'queue' => 'queue1',
        'consumerInstance' => 'consumerClass1',
        'handlers' => [
            0 => 'handlerClassOne::handlerMethodOne',
        ],
        'connection' => 'connection1',
        'maxMessages' => '100',
    ],
    'consumer2' => [
        'name' => 'consumer2',
        'queue' => 'queue2',
        'consumerInstance' => 'consumerClass2',
        'handlers' => [
            0 => 'handlerClassTwo::handlerMethodTwo',
        ],
        'connection' => 'connection2',
        'maxMessages' => null,
    ],
    'consumer3' => [
        'name' => 'consumer3',
        'queue' => 'queue3',
        'consumerInstance' => 'consumerClass3',
        'handlers' => [
            0 => 'handlerClassThree::handlerMethodThree',
        ],
        'connection' => 'amqp',
        'maxMessages' => null,
    ],
    'consumer4' => [
        'name' => 'consumer4',
        'queue' => 'queue4',
        'consumerInstance' => 'Magento\Framework\MessageQueue\ConsumerInterface',
        'handlers' => [
            0 => 'handlerClassFour::handlerMethodFour',
        ],
        'connection' => 'amqp',
        'maxMessages' => null,
    ],
    'consumer5' => [
        'name' => 'consumer5',
        'queue' => 'queue5',
        'consumerInstance' => 'Magento\Framework\MessageQueue\ConsumerInterface',
        'handlers' => [],
        'connection' => 'amqp',
        'maxMessages' => null,
    ],
];