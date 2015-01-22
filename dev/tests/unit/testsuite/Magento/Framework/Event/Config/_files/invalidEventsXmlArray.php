<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'without_event_handle' => [
        '<?xml version="1.0"?><config></config>',
        ["Element 'config': Missing child element(s). Expected is ( event )."],
    ],
    'event_without_required_name_attribute' => [
        '<?xml version="1.0"?><config><event name="some_name"></event></config>',
        ["Element 'event': Missing child element(s). Expected is ( observer )."],
    ],
    'event_with_notallowed_attribute' => [
        '<?xml version="1.0"?><config><event name="somename" notallowed="some value"><observer name="observer_name" ' .
        '/></event></config>',
        ["Element 'event', attribute 'notallowed': The attribute 'notallowed' is not allowed."],
    ],
    'event_with_same_name_attribute_value' => [
        '<?xml version="1.0"?><config><event name="same_name"><observer name="observer_name" /></event><event ' .
        'name="same_name"><observer name="observer_name" /></event></config>',
        [
            "Element 'event': Duplicate key-sequence ['same_name'] in unique identity-constraint " .
            "'uniqueEventName'."
        ],
    ],
    'event_without_required_observer_handle' => [
        '<?xml version="1.0"?><config><event name="some_name"></event></config>',
        ["Element 'event': Missing child element(s). Expected is ( observer )."],
    ],
    'event_without_required_observer_name_attribute' => [
        '<?xml version="1.0"?><config><event name="some_name"><observer shared="true"/></event></config>',
        ["Element 'observer': The attribute 'name' is required but missing."],
    ],
    'event_with_same_observer_handle_name' => [
        '<?xml version="1.0"?><config><event name="some_name"><observer  name="observer_name"/><observer  ' .
        'name="observer_name"/></event></config>',
        [
            "Element 'observer': Duplicate key-sequence ['observer_name'] in unique identity-constraint " .
            "'uniqueObserverName'."
        ],
    ],
    'event_observer_with_invalid_disabled_value' => [
        '<?xml version="1.0"?><config><event name="some_name"><observer ' .
        'name="observer_name" disabled="string"/></event></config>',
        [
            "Element 'observer', attribute 'disabled': 'string' is not a valid value of the atomic type" .
            " 'xs:boolean'."
        ],
    ],
    'event_observer_with_invalid_shared_value' => [
        '<?xml version="1.0"?><config><event name="some_name"><observer ' .
        'name="observer_name" shared="string"/></event></config>',
        [
            "Element 'observer', attribute 'shared': 'string' is not a valid value of the atomic type" .
            " 'xs:boolean'."
        ],
    ],
    'event_observer_with_invalid_method_value' => [
        '<?xml version="1.0"?><config><event name="some_name"><observer ' .
        'name="observer_name" method="_wrong name"/></event></config>',
        [
            "Element 'observer', attribute 'method': [facet 'pattern'] The value '_wrong name' is not accepted by" .
            " the pattern '[a-zA-Z]+'.",
            "Element 'observer', attribute 'method': '_wrong name' is not a valid value of the atomic type " .
            "'methodName'."
        ],
    ]
];
