<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

return [
    'with_notallowed_handle' => [
        '<?xml version="1.0"?><config><notallowe></notallowe></config>',
        [
            "Element 'notallowe': This element is not expected. Expected is one of ( default, stores, websites ).\n" .
            "Line: 1\nThe xml was: \n0:<?xml version=\"1.0\"?>\n1:<config><notallowe/></config>\n2:\n"
        ],
    ]
];
