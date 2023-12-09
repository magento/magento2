<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

return [
    'no arguments' => [
        '<?xml version="1.0"?><arguments xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" />',
        [
            "Element 'arguments': Missing child element(s). Expected is ( argument ).\nLine: 1\nThe xml was: \n" .
            "0:<?xml version=\"1.0\"?>\n1:<arguments xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"/>\n2:\n"
        ],
    ],
    'argument without type' => [
        '<?xml version="1.0"?><arguments xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"><argument/></arguments>',
        [
            "Element 'argument': The type definition is abstract.\nLine: 1\nThe xml was: \n0:<?xml " .
            "version=\"1.0\"?>\n1:<arguments xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">" .
            "<argument/></arguments>\n2:\n"
        ],
    ],
    'forbidden type used' => [
        '<?xml version="1.0"?><arguments xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
        <argument name="a" xsi:type="forbiddenType">v</argument></arguments>',
        [
            "Element 'argument', attribute '{http://www.w3.org/2001/XMLSchema-instance}type': The QName value " .
            "'forbiddenType' of the xsi:type attribute does not resolve to a type definition.\nLine: 2\n" .
            "The xml was: \n0:<?xml version=\"1.0\"?>\n1:<arguments " .
            "xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">\n2:        <argument name=\"a\" " .
            "xsi:type=\"forbiddenType\">v</argument></arguments>\n3:\n",
            "Element 'argument': The type definition is abstract.\nLine: 2\nThe xml was: \n0:<?xml " .
            "version=\"1.0\"?>\n1:<arguments xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">\n" .
            "2:        <argument name=\"a\" xsi:type=\"forbiddenType\">v</argument></arguments>\n3:\n"
        ],
    ],
    'abstract type argumentType used' => [
        '<?xml version="1.0"?><arguments xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
        <argument name="a" xsi:type="argumentType">v</argument></arguments>',
        [
            "Element 'argument': The type definition is abstract.\nLine: 2\nThe xml was: \n" .
            "0:<?xml version=\"1.0\"?>\n1:<arguments xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">\n" .
            "2:        <argument name=\"a\" xsi:type=\"argumentType\">v</argument></arguments>\n3:\n"
        ],
    ],
    'no name attribute' => [
        '<?xml version="1.0"?><arguments xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
        <argument xsi:type="number">v</argument></arguments>',
        [
            "Element 'argument': The attribute 'name' is required but missing.\nLine: 2\nThe xml was: \n" .
            "0:<?xml version=\"1.0\"?>\n1:<arguments xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">\n" .
            "2:        <argument xsi:type=\"number\">v</argument></arguments>\n3:\n"
        ],
    ],
    'forbidden attribute' => [
        '<?xml version="1.0"?><arguments xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
        <argument name="a" xsi:type="string" forbiddenAttribute="w">v</argument></arguments>',
        [
            "Element 'argument', attribute 'forbiddenAttribute': The attribute 'forbiddenAttribute' is not " .
            "allowed.\nLine: 2\nThe xml was: \n0:<?xml version=\"1.0\"?>\n1:<arguments " .
            "xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">\n2:        <argument name=\"a\" " .
            "xsi:type=\"string\" forbiddenAttribute=\"w\">v</argument></arguments>\n3:\n"
        ],
    ],
    'forbidden translate attribute value for string' => [
        '<?xml version="1.0"?><arguments xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
        <argument name="a" xsi:type="string" translate="forbidden">v</argument></arguments>',
        [
            "Element 'argument', attribute 'translate': 'forbidden' is not a valid value of the atomic type " .
            "'xs:boolean'.\nLine: 2\nThe xml was: \n0:<?xml version=\"1.0\"?>\n1:<arguments " .
            "xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">\n2:        <argument name=\"a\" " .
            "xsi:type=\"string\" translate=\"forbidden\">v</argument></arguments>\n3:\n"
        ],
    ],
    'attribute translate for non-string' => [
        '<?xml version="1.0"?><arguments xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
        <argument name="a" xsi:type="boolean" translate="true">true</argument></arguments>',
        [
            "Element 'argument', attribute 'translate': The attribute 'translate' is not allowed.\nLine: 2\nThe " .
            "xml was: \n0:<?xml version=\"1.0\"?>\n1:<arguments " .
            "xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">\n2:        <argument name=\"a\" " .
            "xsi:type=\"boolean\" translate=\"true\">true</argument></arguments>\n3:\n"
        ],
    ],
    'null type should be empty' => [
        '<?xml version="1.0"?><arguments xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
        <argument name="a" xsi:type="null">v</argument></arguments>',
        [
            "Element 'argument': Character content is not allowed, because the content type is empty.\nLine: 2\n" .
            "The xml was: \n0:<?xml version=\"1.0\"?>\n1:<arguments " .
            "xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">\n2:        <argument name=\"a\" " .
            "xsi:type=\"null\">v</argument></arguments>\n3:\n"
        ],
    ],
    'forbidden child node' => [
        '<?xml version="1.0"?><arguments xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
        <argument name="a" xsi:type="string"><child>v</child></argument></arguments>',
        [
            "Element 'child': This element is not expected.\nLine: 2\nThe xml was: \n0:<?xml version=\"1.0\"?>\n" .
            "1:<arguments xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">\n2:        <argument " .
            "name=\"a\" xsi:type=\"string\"><child>v</child></argument></arguments>\n3:\n"
        ],
    ],
    'array with forbidden child' => [
        '<?xml version="1.0"?><arguments xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
        <argument name="a" xsi:type="array"><child>v</child></argument></arguments>',
        [
            "Element 'child': This element is not expected. Expected is ( item ).\nLine: 2\nThe xml was: \n" .
            "0:<?xml version=\"1.0\"?>\n1:<arguments xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">\n" .
            "2:        <argument name=\"a\" xsi:type=\"array\"><child>v</child></argument></arguments>\n3:\n"
        ],
    ],
    'array with 2 same items' => [
        '<?xml version="1.0"?><arguments xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
        <argument name="a" xsi:type="array">
            <item name="name" xsi:type="string">v1</item>
            <item name="name" xsi:type="string">v2</item>
        </argument></arguments>',
        [
            "Element 'item': Duplicate key-sequence ['name'] in key identity-constraint 'argumentItemName'.\n" .
            "Line: 4\nThe xml was: \n0:<?xml version=\"1.0\"?>\n1:<arguments " .
            "xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">\n2:        <argument name=\"a\" " .
            "xsi:type=\"array\">\n3:            <item name=\"name\" xsi:type=\"string\">v1</item>\n" .
            "4:            <item name=\"name\" xsi:type=\"string\">v2</item>\n5:        </argument>" .
            "</arguments>\n6:\n"
        ],
    ],
    'array item without name' => [
        '<?xml version="1.0"?><arguments xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
        <argument name="a" xsi:type="array"><item xsi:type="string">v</item></argument></arguments>',
        [
            "Element 'item': The attribute 'name' is required but missing.\nLine: 2\nThe xml was: \n" .
            "0:<?xml version=\"1.0\"?>\n1:<arguments xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">\n" .
            "2:        <argument name=\"a\" xsi:type=\"array\"><item xsi:type=\"string\">v</item></argument>" .
            "</arguments>\n3:\n",
            "Element 'item': Not all fields of key identity-constraint 'argumentItemName' evaluate to a node.\n" .
            "Line: 2\nThe xml was: \n0:<?xml version=\"1.0\"?>\n1:<arguments " .
            "xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">\n2:        <argument name=\"a\" " .
            "xsi:type=\"array\"><item xsi:type=\"string\">v</item></argument></arguments>\n3:\n"
        ],
    ],
    'array item with forbidden child' => [
        '<?xml version="1.0"?><arguments xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
        <argument name="a" xsi:type="array">
            <item name="item" xsi:type="string"><child>v</child></item>
        </argument></arguments>',
        [
            "Element 'child': This element is not expected.\nLine: 3\nThe xml was: \n0:<?xml version=\"1.0\"?>\n" .
            "1:<arguments xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">\n2:        <argument " .
            "name=\"a\" xsi:type=\"array\">\n3:            <item name=\"item\" xsi:type=\"string\">" .
            "<child>v</child></item>\n4:        </argument></arguments>\n5:\n"
        ],
    ],
    'nested array with same named items' => [
        '<?xml version="1.0"?><arguments xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
        <argument name="a" xsi:type="array">
            <item name="item1" xsi:type="string">v</item>
            <item name="item2" xsi:type="array">
                <item name="item1" xsi:type="string">v</item>
            </item>
            <item name="item3" xsi:type="array">
                <item name="item4" xsi:type="string">v</item>
                <item name="item4" xsi:type="string">v</item>
            </item>
        </argument></arguments>',
        [
            "Element 'item': Duplicate key-sequence ['item4'] in key identity-constraint 'itemName'.\n" .
            "Line: 9\nThe xml was: \n4:            <item name=\"item2\" xsi:type=\"array\">\n" .
            "5:                <item name=\"item1\" xsi:type=\"string\">v</item>\n6:            </item>\n" .
            "7:            <item name=\"item3\" xsi:type=\"array\">\n8:                <item name=\"item4\" " .
            "xsi:type=\"string\">v</item>\n9:                <item name=\"item4\" xsi:type=\"string\">v</item>\n" .
            "10:            </item>\n11:        </argument></arguments>\n12:\n"
        ],
    ]
];
