<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'without_router_handle' => [
        '<?xml version="1.0"?><config></config>',
        ["Element 'config': Missing child element(s). Expected is ( router ).\nLine: 1\n"],
    ],
    'router_without_required_id_attribute' => [
        '<?xml version="1.0"?><config><router><route id="first"> <module name="Some_ModuleName" />' .
        '</route></router></config>',
        ["Element 'router': The attribute 'id' is required but missing.\nLine: 1\n"],
    ],
    'route_with_same_id_attribute' => [
        '<?xml version="1.0"?><config><router id="some"><route id="first"><module name="Some_ModuleName" />' .
        '</route><route id="first" frontName="test_test" ><module name="Some_ModuleName" />' .
        '</route></router></config>',
        ["Element 'route': Duplicate key-sequence ['first'] in unique identity-constraint 'uniqueRouteId'.\nLine: 1\n"],
    ],
    'router_without_required_route_handle' => [
        '<?xml version="1.0"?><config><router id="first"></router></config>',
        ["Element 'router': Missing child element(s). Expected is ( route ).\nLine: 1\n"],
    ],
    'routers_with_same_id' => [
        '<?xml version="1.0"?><config><router id="first"><route id="first_route"><module name="Some_ModuleName" />' .
        '</route></router><router id="first"><route id="test"><module name="Some_ModuleName" ' .
        'before="asdasd" /></route></router></config>',
        [
            "Element 'router': Duplicate key-sequence ['first'] in unique identity-constraint" .
            " 'uniqueRouterId'.\nLine: 1\n"],
    ],
    'router_with_notallowed_attribute' => [
        '<?xml version="1.0"?><config><router id="first" notallowed="text"><route id="first_route">' .
        '<module name="Some_ModuleName" /></route></router></config>',
        ["Element 'router', attribute 'notallowed': The attribute 'notallowed' is not allowed.\nLine: 1\n"],
    ],
    'route_without_required_module_handle' => [
        '<?xml version="1.0"?><config><router id="first" ><route id="first_route"></route></router></config>',
        ["Element 'route': Missing child element(s). Expected is ( module ).\nLine: 1\n"],
    ],
    'route_with_notallowed_attribute' => [
        '<?xml version="1.0"?><config><router id="first"><route id="first_route" notallowe="text"><module' .
        ' name="Some_ModuleName" /></route></router></config>',
        ["Element 'route', attribute 'notallowe': The attribute 'notallowe' is not allowed.\nLine: 1\n"],
    ],
    'same_route_frontname_attribute_value' => [
        '<?xml version="1.0"?><config><router id="first"><route id="first_route" frontName="test_test">' .
        '<module name="Some_ModuleName" /></route><route id="second_route" frontName="test_test">' .
        '<module name="Some_ModuleName" /></route></router></config>',
        [
            "Element 'route': Duplicate key-sequence ['test_test'] in unique " .
            "identity-constraint 'uniqueRouteFrontName'.\nLine: 1\n"
        ],
    ],
    'module_with_notallowed_attribute' => [
        '<?xml version="1.0"?><config><router id="first"><route id="first_route"><module ' .
        'name="Some_ModuleName" notallowed="text" /></route></router></config>',
        ["Element 'module', attribute 'notallowed': The attribute 'notallowed' is not allowed.\nLine: 1\n"],
    ],
    'router_id_empty_value' => [
        '<?xml version="1.0"?><config><router id=""><route id="first_route"><module name="Some_ModuleName" />' .
        '</route></router></config>',
        [
            "Element 'router', attribute 'id': [facet 'pattern'] The value '' is not accepted by the " .
            "pattern '[A-Za-z0-9\-_]{3,}'.\nLine: 1\n",
            "Element 'router', attribute 'id': '' is not a valid value of the atomic type 'routerIdType'.\nLine: 1\n",
            "Element 'router', attribute 'id': " .
            "Warning: No precomputed value available, the value was either invalid or " .
            "something strange happend.\nLine: 1\n"
        ],
    ],
    'router_id_value_regexp1' => [
        '<?xml version="1.0"?><config><router id="as"><route id="first_route"><module name="Some_ModuleName" />' .
        '</route></router></config>',
        [
            "Element 'router', attribute 'id': [facet 'pattern'] The value 'as' is not accepted by the " .
            "pattern '[A-Za-z0-9\-_]{3,}'.\nLine: 1\n",
            "Element 'router', attribute 'id': 'as' is not a valid value of the atomic type 'routerIdType'.\nLine: 1\n",
            "Element 'router', attribute 'id': " .
            "Warning: No precomputed value available, the value was either invalid or " .
            "something strange happend.\nLine: 1\n"
        ],
    ],
    'router_id_value_regexp2' => [
        '<?xml version="1.0"?><config><router id="##%#"><route id="first_route"><module name="Some_ModuleName" />' .
        '</route></router></config>',
        [
            "Element 'router', attribute 'id': [facet 'pattern'] The value '##%#' is not accepted by the " .
            "pattern '[A-Za-z0-9\-_]{3,}'.\nLine: 1\n",
            "Element 'router', attribute 'id': '##%#' is not a valid value of the atomic type" .
            " 'routerIdType'.\nLine: 1\n",
            "Element 'router', attribute 'id': " .
            "Warning: No precomputed value available, the value was either invalid or " .
            "something strange happend.\nLine: 1\n"
        ],
    ],
    'router_route_value_regexp1' => [
        '<?xml version="1.0"?><config><router id="first"><route id="dc"><module name="Some_ModuleName" />' .
        '</route></router></config>',
        [
            "Element 'route', attribute 'id': [facet 'pattern'] The value 'dc' is not accepted by the " .
            "pattern '[A-Za-z0-9_]{3,}'.\nLine: 1\n",
            "Element 'route', attribute 'id': 'dc' is not a valid value of the atomic type 'routeIdType'.\nLine: 1\n",
            "Element 'route', attribute 'id': Warning: No precomputed value available, the value was either " .
            "invalid or something strange happend.\nLine: 1\n"
        ],
    ],
    'router_route_empty_before_attribute_value' => [
        '<?xml version="1.0"?><config><router id="first"><route id="test"><module name="Some_ModuleName" ' .
        'before="" /></route></router></config>',
        [
            "Element 'module', attribute 'before': [facet 'pattern'] The value '' is not accepted by the " .
            "pattern '[A-Za-z0-9_]{3,}'.\nLine: 1\n",
            "Element 'module', attribute 'before': '' is not a valid value of the atomic type" .
            " 'beforeAfterType'.\nLine: 1\n"
        ],
    ],
    'router_route_before_attribute_value_regexp1' => [
        '<?xml version="1.0"?><config><router id="first"><route id="test"><module ' .
        'name="Some_ModuleName" before="!!!!" /></route></router></config>',
        [
            "Element 'module', attribute 'before': [facet 'pattern'] The value '!!!!' is not accepted by the " .
            "pattern '[A-Za-z0-9_]{3,}'.\nLine: 1\n",
            "Element 'module', attribute 'before': '!!!!' is not a valid value of the atomic type" .
            " 'beforeAfterType'.\nLine: 1\n"
        ],
    ],
    'router_route_before_attribute_value_regexp2' => [
        '<?xml version="1.0"?><config><router id="first"><route id="test">' .
        '<module name="Some_ModuleName" before="ab" /></route></router></config>',
        [
            "Element 'module', attribute 'before': [facet 'pattern'] The value 'ab' is not accepted by " .
            "the pattern '[A-Za-z0-9_]{3,}'.\nLine: 1\n",
            "Element 'module', attribute 'before': 'ab' is not a valid value of the atomic type" .
            " 'beforeAfterType'.\nLine: 1\n"
        ],
    ],
    'route_module_without_required_name_atrribute' => [
        '<?xml version="1.0"?><config><router id="first"><route id="test"><module /></route></router></config>',
        ["Element 'module': The attribute 'name' is required but missing.\nLine: 1\n"],
    ],
    'route_module_name_attribute_value_regexp1' => [
        '<?xml version="1.0"?><config><router id="first"><route id="test"><module name="ss" />' .
        '</route></router></config>',
        [
            "Element 'module', attribute 'name': [facet 'pattern'] The value 'ss' is not accepted by the " .
            "pattern '[A-Za-z0-9_]{3,}'.\nLine: 1\n",
            "Element 'module', attribute 'name': 'ss' is not a valid value of the atomic type" .
            " 'moduleNameType'.\nLine: 1\n",
            "Element 'module', attribute 'name': Warning: No precomputed value available, the value was either " .
            "invalid or something strange happend.\nLine: 1\n"
        ],
    ],
    'route_module_name_attribute_value_regexp2' => [
        '<?xml version="1.0"?><config><router id="firsst"><route id="test"><module name="#$%^" />' .
        '</route></router></config>',
        [
            "Element 'module', attribute 'name': [facet 'pattern'] The value '#$%^' is not accepted by " .
            "the pattern '[A-Za-z0-9_]{3,}'.\nLine: 1\n",
            "Element 'module', attribute 'name': '#$%^' is not a valid value of the atomic type" .
            " 'moduleNameType'.\nLine: 1\n",
            "Element 'module', attribute 'name': Warning: No precomputed value available, the value was either " .
            "invalid or something strange happend.\nLine: 1\n"
        ],
    ],
    'route_module_before_attribute_empty_value' => [
        '<?xml version="1.0"?><config><router id="firsst"><route id="test">' .
        '<module name="Some_ModuleName" before="" /></route></router></config>',
        [
            "Element 'module', attribute 'before': [facet 'pattern'] The value '' is not accepted by " .
            "the pattern '[A-Za-z0-9_]{3,}'.\nLine: 1\n",
            "Element 'module', attribute 'before': '' is not a valid value of the atomic type" .
            " 'beforeAfterType'.\nLine: 1\n"
        ],
    ],
    'route_module_before_attribute_value_regexp1' => [
        '<?xml version="1.0"?><config><router id="firsst"><route id="test">' .
        '<module name="Some_ModuleName" before="qq" /></route></router></config>',
        [
            "Element 'module', attribute 'before': [facet 'pattern'] The value 'qq' is not accepted by the " .
            "pattern '[A-Za-z0-9_]{3,}'.\nLine: 1\n",
            "Element 'module', attribute 'before': 'qq' is not a valid value of the atomic type" .
            " 'beforeAfterType'.\nLine: 1\n"
        ],
    ],
    'route_module_before_attribute_value_regexp2' => [
        '<?xml version="1.0"?><config><router id="firsst"><route id="test">' .
        '<module name="Some_ModuleName" before="!!!!" /></route></router></config>',
        [
            "Element 'module', attribute 'before': [facet 'pattern'] The value '!!!!' is not accepted by the " .
            "pattern '[A-Za-z0-9_]{3,}'.\nLine: 1\n",
            "Element 'module', attribute 'before': '!!!!' is not a valid value of the atomic type" .
            " 'beforeAfterType'.\nLine: 1\n"
        ],
    ],
    'route_module_after_attribute_empty_value' => [
        '<?xml version="1.0"?><config><router id="firsst"><route id="test"><module name="Some_ModuleName" after="" />' .
        '</route></router></config>',
        [
            "Element 'module', attribute 'after': [facet 'pattern'] The value '' is not accepted " .
            "by the pattern '[A-Za-z0-9_]{3,}'.\nLine: 1\n",
            "Element 'module', attribute 'after': '' is not a valid value of the atomic type" .
            " 'beforeAfterType'.\nLine: 1\n"
        ],
    ],
    'route_module_after_attribute_value_regexp1' => [
        '<?xml version="1.0"?><config><router id="first">' .
        '<route id="test"><module name="Some_ModuleName" after="sd" />' .
        '</route></router></config>',
        [
            "Element 'module', attribute 'after': [facet 'pattern'] The value 'sd' is not accepted by" .
            " the pattern '[A-Za-z0-9_]{3,}'.\nLine: 1\n",
            "Element 'module', attribute 'after': 'sd' is not a valid value of the atomic type" .
            " 'beforeAfterType'.\nLine: 1\n"
        ],
    ],
    'route_module_after_attribute_value_regexp2' => [
        '<?xml version="1.0"?><config><router id="first"><route id="test">' .
        '<module name="Some_ModuleName" after="!!!!" /></route></router></config>',
        [
            "Element 'module', attribute 'after': [facet 'pattern'] The value '!!!!' is not accepted by the " .
            "pattern '[A-Za-z0-9_]{3,}'.\nLine: 1\n",
            "Element 'module', attribute 'after': '!!!!' is not a valid value of the atomic type" .
            " 'beforeAfterType'.\nLine: 1\n"
        ],
    ]
];
