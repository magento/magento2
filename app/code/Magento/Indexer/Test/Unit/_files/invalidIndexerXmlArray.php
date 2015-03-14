<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'without_indexer_handle' => [
        '<?xml version="1.0"?><config></config>',
        ["Element 'config': Missing child element(s). Expected is ( indexer )."],
    ],
    'indexer_with_notallowed_attribute' => [
        '<?xml version="1.0"?><config>' .
        '<indexer id="somename" view_id="view_01" class="Class\Name" notallowed="some value">' .
        '<title>Test</title><description>Test</description></indexer></config>',
        ["Element 'indexer', attribute 'notallowed': The attribute 'notallowed' is not allowed."],
    ],
    'indexer_without_view_attribute' => [
        '<?xml version="1.0"?><config><indexer id="somename" class="Class\Name">' .
        '<title>Test</title><description>Test</description></indexer></config>',
        ["Element 'indexer': The attribute 'view_id' is required but missing."],
    ],
    'indexer_duplicate_view_attribute' => [
        '<?xml version="1.0"?><config><indexer id="somename" view_id="view_01" class="Class\Name">' .
        '<title>Test</title><description>Test</description></indexer>' .
        '<indexer id="somename_two" view_id="view_01" class="Class\Name">' .
        '<title>Test</title><description>Test</description></indexer></config>',
        ["Element 'indexer': Duplicate key-sequence ['view_01'] in unique identity-constraint 'uniqueViewId'."],
    ],
    'indexer_without_title' => [
        '<?xml version="1.0"?><config><indexer id="somename" view_id="view_01" class="Class\Name">' .
        '<description>Test</description></indexer></config>',
        ["Element 'description': This element is not expected. Expected is ( title )."],
    ],
    'indexer_without_description' => [
        '<?xml version="1.0"?><config><indexer id="somename" view_id="view_01" class="Class\Name">' .
        '<title>Test</title></indexer></config>',
        ["Element 'indexer': Missing child element(s). Expected is ( description )."],
    ]
];
