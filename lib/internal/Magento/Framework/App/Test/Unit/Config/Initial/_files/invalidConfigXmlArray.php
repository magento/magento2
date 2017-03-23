<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'with_notallowed_handle' => [
        '<?xml version="1.0"?><config><notallowe></notallowe></config>',
        [
            "Element 'notallowe': This element is not expected. Expected is one of" .
            " ( default, stores, websites ).\nLine: 1\n"
        ],
    ]
];
