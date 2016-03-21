<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'without_indexer_handle' => [
        '<?xml version="1.0"?><config></config>',
        ["Element 'config': Missing child element(s). Expected is ( indexer ).\nLine: 1\n"],
    ],
    'indexer_with_notallowed_attribute' => [
        '<?xml version="1.0"?><config>' .
        '<indexer id="somename" view_id="view_01" class="Class\Name" notallowed="some value">' .
        '<title>Test</title><description>Test</description></indexer></config>',
        ["Element 'indexer', attribute 'notallowed': The attribute 'notallowed' is not allowed.\nLine: 1\n"],
    ],
    'indexer_without_view_attribute' => [
        '<?xml version="1.0"?><config><indexer id="somename" class="Class\Name">' .
        '<title>Test</title><description>Test</description></indexer></config>',
        ["Element 'indexer': The attribute 'view_id' is required but missing.\nLine: 1\n"],
    ],
    'indexer_duplicate_view_attribute' => [
        '<?xml version="1.0"?><config><indexer id="somename" view_id="view_01" class="Class\Name">' .
        '<title>Test</title><description>Test</description></indexer>' .
        '<indexer id="somename_two" view_id="view_01" class="Class\Name">' .
        '<title>Test</title><description>Test</description></indexer></config>',
        [
            "Element 'indexer': Duplicate key-sequence ['view_01'] in unique identity-constraint"
                . " 'uniqueViewId'.\nLine: 1\n"
        ],
    ],
];
