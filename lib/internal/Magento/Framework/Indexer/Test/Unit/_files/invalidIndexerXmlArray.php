<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

return [
    'without_indexer_handle' => [
        '<?xml version="1.0"?><config></config>',
        [
            [
                "Element 'config': Missing child element(s). Expected is ( indexer ).\nLine: 1\nThe xml was: \n" .
                "0:<?xml version=\"1.0\"?>\n1:<config/>\n2:\n",
                false,
            ],
        ],
    ],
    'indexer_with_notallowed_attribute' => [
        '<?xml version="1.0"?><config>' .
        '<indexer id="somename" view_id="view_01" class="Class\Name" notallowed="some value">' .
        '<title>Test</title><description>Test</description></indexer></config>',
        [
            [
                "Element 'indexer', attribute 'notallowed': The attribute 'notallowed' is not allowed.\nLine: 1\n" .
                "The xml was: \n0:<?xml version=\"1.0\"?>\n1:<config><indexer id=\"somename\" view_id=\"view_01\" " .
                "class=\"Class\Name\" notallowed=\"some value\"><title>Test</title><description>Test</description>" .
                "</indexer></config>\n2:\n",
                false,
            ],
        ],
    ],
    'indexer_without_view_attribute' => [
        '<?xml version="1.0"?><config><indexer id="somename" class="Class\Name">' .
        '<title>Test</title><description>Test</description></indexer></config>',
        [
            [
                "Element 'indexer': The attribute 'view_id' is required but missing.\nLine: 1\nThe xml was: \n" .
                "0:<?xml version=\"1.0\"?>\n1:<config><indexer id=\"somename\" class=\"Class\Name\"><title>" .
                "Test</title><description>Test</description></indexer></config>\n2:\n",
                false,
            ],
        ],
    ],
    'indexer_with_wrong_class_name' => [
        '<?xml version="1.0"?><config><indexer id="somename" view_id="view_01" class="Class+\Name">' .
        '<title>Test</title><description>Test</description></indexer></config>',
        [
            [
                "/Element \'indexer\', attribute \'class\': .*\'Class\+\\\\Name\' is not (a valid value|accepted).*/",
                true,
            ],
        ],
    ],
    'indexer_duplicate_view_attribute' => [
        '<?xml version="1.0"?><config><indexer id="somename" view_id="view_01" class="Class\Name">' .
        '<title>Test</title><description>Test</description></indexer>' .
        '<indexer id="somename_two" view_id="view_01" class="Class\Name">' .
        '<title>Test</title><description>Test</description></indexer></config>',
        [
            [
                "Element 'indexer': Duplicate key-sequence ['view_01'] in unique identity-constraint " .
                "'uniqueViewId'.\nLine: 1\nThe xml was: \n0:<?xml version=\"1.0\"?>\n1:<config><indexer " .
                "id=\"somename\" view_id=\"view_01\" class=\"Class\Name\"><title>Test</title><description>Test" .
                "</description></indexer><indexer id=\"somename_two\" view_id=\"view_01\" class=\"Class\Name\">" .
                "<title>Test</title><description>Test</description></indexer></config>\n2:\n",
                false,
            ],
        ],
    ],
];
