<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    [
        'command' => 'fake:command',
        'config' => [
            // arguments
            'foo',
            'bar',

            // options
            '--option1' => 'baz',
            '-option2' => 'qux',
        ]
    ],
];
