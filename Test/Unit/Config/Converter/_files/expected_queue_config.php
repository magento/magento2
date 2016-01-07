<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    'q.01' =>
        [
            'name' => 'q.01',
            'exchange' => 'ex.01',
            'consumer' => 'cons.01',
            'consumerInstance' => '\\Magento\\Consumer\\Instance',
            'type' => 'amqp',
            'topics' =>
                [
                    'top.01' =>
                        [
                            'name' => 'top.01',
                            'handlerName' => 'h.01',
                            'handler' => NULL,
                        ],
                    'top.02' =>
                        [
                            'name' => 'top.02',
                            'handlerName' => 'h.02',
                            'handler' => NULL,
                        ],
                    'top.03' =>
                        [
                            'name' => 'top.03',
                            'handlerName' => 'h.03',
                            'handler' => NULL,
                        ],
                ],
        ],
    'q.02' =>
        [
            'name' => 'q.02',
            'exchange' => 'ex.01',
            'consumer' => 'cons.01',
            'consumerInstance' => '\\Magento\\Consumer\\Instance',
            'type' => 'db',
            'topics' =>
                [
                    'top.01' =>
                        [
                            'name' => 'top.01',
                            'handlerName' => NULL,
                            'handler' => NULL,
                        ],
                    'top.02' =>
                        [
                            'name' => 'top.02',
                            'handlerName' => NULL,
                            'handler' => '\\Magento\\Handler\\Class\\Name::methodName',
                        ],
                    'top.03' =>
                        [
                            'name' => 'top.03',
                            'handlerName' => 'h.03',
                            'handler' => NULL,
                        ],
                ],
        ],
];
