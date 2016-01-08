<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    'topics' => [
        'top.01' => [
            'name' => 'top.01',
            'type' => 'amqp',
            'exchange' => null,
            'consumerInstance' => null,
            'handlerName' => 'h.01',
            'handler' => null,
            'maxMessages' => null,
            'queues' => [
                'q.01' => [
                    'name' => 'q.01',
                    'handlerName' => null,
                    'handler' => null,
                    'exchange' => 'ex.01',
                    'consumer' => 'cons.01',
                    'consumerInstance' => 'Magento\\Consumer\\Instance',
                    'maxMessages' => '512',
                    'type' => null,
                ]
            ],
        ],
        'top.02' => [
            'name' => 'top.02',
            'type' => 'db',
            'exchange' => null,
            'consumerInstance' => null,
            'handlerName' => null,
            'handler' => 'Magento\Handler\Class\Name::methodName',
            'maxMessages' => null,
            'queues' => [
                'q.02' => [
                    'name' => 'q.02',
                    'handlerName' => null,
                    'handler' => null,
                    'exchange' => 'ex.01',
                    'consumer' => 'cons.02',
                    'consumerInstance' => 'Magento\\Consumer\\Instance',
                    'maxMessages' => '512',
                    'type' => null,
                ]
            ],
        ],
        'top.03' => [
            'name' => 'top.03',
            'type' => null,
            'exchange' => null,
            'consumerInstance' => null,
            'handlerName' => null,
            'handler' => null,
            'maxMessages' => null,
            'queues' => [
                'q.03' => [
                    'name' => 'q.03',
                    'handlerName' => null,
                    'handler' => null,
                    'exchange' => null,
                    'consumer' => 'cons.03',
                    'consumerInstance' => null,
                    'maxMessages' => null,
                    'type' => 'db'
                ]
            ],
        ],
        'top.04' => [
            'name' => 'top.04',
            'type' => 'amqp',
            'exchange' => null,
            'consumerInstance' => null,
            'handlerName' => null,
            'handler' => null,
            'maxMessages' => null,
            'queues' => [
                'q.04' => [
                    'name' => 'q.04',
                    'handlerName' => 'h.04',
                    'handler' => null,
                    'exchange' => 'ex.01',
                    'consumer' => 'cons.04',
                    'consumerInstance' => 'Magento\Consumer\Instance',
                    'maxMessages' => '512',
                    'type' => null,
                ],
                'q.05' => [
                    'name' => 'q.05',
                    'handlerName' => 'h.05',
                    'handler' => null,
                    'exchange' => 'ex.01',
                    'consumer' => 'cons.05',
                    'consumerInstance' => 'Magento\Consumer\Instance',
                    'maxMessages' => '512',
                    'type' => 'db',
                ],
                'q.06' => [
                    'name' => 'q.06',
                    'handlerName' => 'h.06',
                    'handler' => null,
                    'exchange' => 'ex.01',
                    'consumer' => 'cons.06',
                    'consumerInstance' => 'Magento\Consumer\Instance',
                    'maxMessages' => '512',
                    'type' => null,
                ],
            ]
        ],
        'top.05' => [
            'name' => 'top.05',
            'type' => null,
            'exchange' => 'ex.01',
            'consumerInstance' => 'Magento\Consumer\Instance',
            'handlerName' => null,
            'handler' => null,
            'maxMessages' => '512',
            'queues' => [
                'q.07' => [
                    'name' => 'q.07',
                    'handlerName' => 'h.07',
                    'handler' => null,
                    'exchange' => null,
                    'consumer' => 'cons.07',
                    'consumerInstance' => null,
                    'maxMessages' => null,
                    'type' => 'db',
                ],
                'q.08' => [
                    'name' => 'q.08',
                    'handler' => 'Magento\Handler\Class\Name::methodName',
                    'handlerName' => null,
                    'exchange' => null,
                    'consumer' => 'cons.08',
                    'consumerInstance' => null,
                    'maxMessages' => null,
                    'type' => 'amqp',
                ],
                'q.09' => [
                    'name' => 'q.09',
                    'handlerName' => 'h.09',
                    'handler' => null,
                    'exchange' => null,
                    'consumer' => 'cons.09',
                    'consumerInstance' => null,
                    'maxMessages' => null,
                    'type' => 'db',
                ],
            ]
        ]
    ],
];
