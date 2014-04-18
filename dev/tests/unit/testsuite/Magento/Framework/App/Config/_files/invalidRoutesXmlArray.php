<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
return array(
    'without_router_handle' => array(
        '<?xml version="1.0"?><config></config>',
        array("Element 'config': Missing child element(s). Expected is ( router ).")
    ),
    'router_without_required_id_attribute' => array(
        '<?xml version="1.0"?><config><router><route id="first"> <module name="Some_ModuleName" />' .
        '</route></router></config>',
        array("Element 'router': The attribute 'id' is required but missing.")
    ),
    'route_with_same_id_attribute' => array(
        '<?xml version="1.0"?><config><router id="some"><route id="first"><module name="Some_ModuleName" />' .
        '</route><route id="first" frontName="test_test" ><module name="Some_ModuleName" />' .
        '</route></router></config>',
        array("Element 'route': Duplicate key-sequence ['first'] in unique identity-constraint 'uniqueRouteId'.")
    ),
    'router_without_required_route_handle' => array(
        '<?xml version="1.0"?><config><router id="first"></router></config>',
        array("Element 'router': Missing child element(s). Expected is ( route ).")
    ),
    'routers_with_same_id' => array(
        '<?xml version="1.0"?><config><router id="first"><route id="first_route"><module name="Some_ModuleName" />' .
        '</route></router><router id="first"><route id="test"><module name="Some_ModuleName" ' .
        'before="asdasd" /></route></router></config>',
        array("Element 'router': Duplicate key-sequence ['first'] in unique identity-constraint 'uniqueRouterId'.")
    ),
    'router_with_notallowed_attribute' => array(
        '<?xml version="1.0"?><config><router id="first" notallowed="text"><route id="first_route">' .
        '<module name="Some_ModuleName" /></route></router></config>',
        array("Element 'router', attribute 'notallowed': The attribute 'notallowed' is not allowed.")
    ),
    'route_without_required_module_handle' => array(
        '<?xml version="1.0"?><config><router id="first" ><route id="first_route"></route></router></config>',
        array("Element 'route': Missing child element(s). Expected is ( module ).")
    ),
    'route_with_notallowed_attribute' => array(
        '<?xml version="1.0"?><config><router id="first"><route id="first_route" notallowe="text"><module' .
        ' name="Some_ModuleName" /></route></router></config>',
        array("Element 'route', attribute 'notallowe': The attribute 'notallowe' is not allowed.")
    ),
    'same_route_frontname_attribute_value' => array(
        '<?xml version="1.0"?><config><router id="first"><route id="first_route" frontName="test_test">' .
        '<module name="Some_ModuleName" /></route><route id="second_route" frontName="test_test">' .
        '<module name="Some_ModuleName" /></route></router></config>',
        array(
            "Element 'route': Duplicate key-sequence ['test_test'] in unique " .
            "identity-constraint 'uniqueRouteFrontName'."
        )
    ),
    'module_with_notallowed_attribute' => array(
        '<?xml version="1.0"?><config><router id="first"><route id="first_route"><module ' .
        'name="Some_ModuleName" notallowed="text" /></route></router></config>',
        array("Element 'module', attribute 'notallowed': The attribute 'notallowed' is not allowed.")
    ),
    'router_id_empty_value' => array(
        '<?xml version="1.0"?><config><router id=""><route id="first_route"><module name="Some_ModuleName" />' .
        '</route></router></config>',
        array(
            "Element 'router', attribute 'id': [facet 'pattern'] The value '' is not accepted by the " .
            "pattern '[A-Za-z0-9\-_]{3,}'.",
            "Element 'router', attribute 'id': '' is not a valid value of the atomic type 'routerIdType'.",
            "Element 'router', attribute 'id': ".
            "Warning: No precomputed value available, the value was either invalid or " .
            "something strange happend."
        )
    ),
    'router_id_value_regexp1' => array(
        '<?xml version="1.0"?><config><router id="as"><route id="first_route"><module name="Some_ModuleName" />' .
        '</route></router></config>',
        array(
            "Element 'router', attribute 'id': [facet 'pattern'] The value 'as' is not accepted by the " .
            "pattern '[A-Za-z0-9\-_]{3,}'.",
            "Element 'router', attribute 'id': 'as' is not a valid value of the atomic type 'routerIdType'.",
            "Element 'router', attribute 'id': ".
            "Warning: No precomputed value available, the value was either invalid or " .
            "something strange happend."
        )
    ),
    'router_id_value_regexp2' => array(
        '<?xml version="1.0"?><config><router id="#%#%#"><route id="first_route"><module name="Some_ModuleName" />' .
        '</route></router></config>',
        array(
            "Element 'router', attribute 'id': [facet 'pattern'] The value '#%#%#' is not accepted by the " .
            "pattern '[A-Za-z0-9\-_]{3,}'.",
            "Element 'router', attribute 'id': '#%#%#' is not a valid value of the atomic type 'routerIdType'.",
            "Element 'router', attribute 'id': ".
            "Warning: No precomputed value available, the value was either invalid or " .
            "something strange happend."
        )
    ),
    'router_route_value_regexp1' => array(
        '<?xml version="1.0"?><config><router id="first"><route id="dc"><module name="Some_ModuleName" />' .
        '</route></router></config>',
        array(
            "Element 'route', attribute 'id': [facet 'pattern'] The value 'dc' is not accepted by the " .
            "pattern '[A-Za-z_]{3,}'.",
            "Element 'route', attribute 'id': 'dc' is not a valid value of the atomic type 'routeIdType'.",
            "Element 'route', attribute 'id': Warning: No precomputed value available, the value was either " .
            "invalid or something strange happend."
        )
    ),
    'router_route_empty_before_attribute_value' => array(
        '<?xml version="1.0"?><config><router id="first"><route id="test"><module name="Some_ModuleName" ' .
        'before="" /></route></router></config>',
        array(
            "Element 'module', attribute 'before': [facet 'pattern'] The value '' is not accepted by the " .
            "pattern '[A-Za-z0-9_]{3,}'.",
            "Element 'module', attribute 'before': '' is not a valid value of the atomic type 'beforeAfterType'."
        )
    ),
    'router_route_before_attribute_value_regexp1' => array(
        '<?xml version="1.0"?><config><router id="first"><route id="test"><module ' .
        'name="Some_ModuleName" before="!!!!" /></route></router></config>',
        array(
            "Element 'module', attribute 'before': [facet 'pattern'] The value '!!!!' is not accepted by the " .
            "pattern '[A-Za-z0-9_]{3,}'.",
            "Element 'module', attribute 'before': '!!!!' is not a valid value of the atomic type 'beforeAfterType'."
        )
    ),
    'router_route_before_attribute_value_regexp2' => array(
        '<?xml version="1.0"?><config><router id="first"><route id="test">' .
        '<module name="Some_ModuleName" before="ab" /></route></router></config>',
        array(
            "Element 'module', attribute 'before': [facet 'pattern'] The value 'ab' is not accepted by " .
            "the pattern '[A-Za-z0-9_]{3,}'.",
            "Element 'module', attribute 'before': 'ab' is not a valid value of the atomic type 'beforeAfterType'."
        )
    ),
    'route_module_without_required_name_atrribute' => array(
        '<?xml version="1.0"?><config><router id="first"><route id="test"><module /></route></router></config>',
        array("Element 'module': The attribute 'name' is required but missing.")
    ),
    'route_module_name_attribute_value_regexp1' => array(
        '<?xml version="1.0"?><config><router id="first"><route id="test"><module name="ss" />' .
        '</route></router></config>',
        array(
            "Element 'module', attribute 'name': [facet 'pattern'] The value 'ss' is not accepted by the " .
            "pattern '[A-Za-z0-9_]{3,}'.",
            "Element 'module', attribute 'name': 'ss' is not a valid value of the atomic type 'moduleNameType'.",
            "Element 'module', attribute 'name': Warning: No precomputed value available, the value was either " .
            "invalid or something strange happend."
        )
    ),
    'route_module_name_attribute_value_regexp2' => array(
        '<?xml version="1.0"?><config><router id="firsst"><route id="test"><module name="#$%^" />' .
        '</route></router></config>',
        array(
            "Element 'module', attribute 'name': [facet 'pattern'] The value '#$%^' is not accepted by " .
            "the pattern '[A-Za-z0-9_]{3,}'.",
            "Element 'module', attribute 'name': '#$%^' is not a valid value of the atomic type 'moduleNameType'.",
            "Element 'module', attribute 'name': Warning: No precomputed value available, the value was either " .
            "invalid or something strange happend."
        )
    ),
    'route_module_before_attribute_empty_value' => array(
        '<?xml version="1.0"?><config><router id="firsst"><route id="test">' .
        '<module name="Some_ModuleName" before="" /></route></router></config>',
        array(
            "Element 'module', attribute 'before': [facet 'pattern'] The value '' is not accepted by " .
            "the pattern '[A-Za-z0-9_]{3,}'.",
            "Element 'module', attribute 'before': '' is not a valid value of the atomic type 'beforeAfterType'."
        )
    ),
    'route_module_before_attribute_value_regexp1' => array(
        '<?xml version="1.0"?><config><router id="firsst"><route id="test">' .
        '<module name="Some_ModuleName" before="qq" /></route></router></config>',
        array(
            "Element 'module', attribute 'before': [facet 'pattern'] The value 'qq' is not accepted by the " .
            "pattern '[A-Za-z0-9_]{3,}'.",
            "Element 'module', attribute 'before': 'qq' is not a valid value of the atomic type 'beforeAfterType'."
        )
    ),
    'route_module_before_attribute_value_regexp2' => array(
        '<?xml version="1.0"?><config><router id="firsst"><route id="test">' .
        '<module name="Some_ModuleName" before="!!!!" /></route></router></config>',
        array(
            "Element 'module', attribute 'before': [facet 'pattern'] The value '!!!!' is not accepted by the " .
            "pattern '[A-Za-z0-9_]{3,}'.",
            "Element 'module', attribute 'before': '!!!!' is not a valid value of the atomic type 'beforeAfterType'."
        )
    ),
    'route_module_after_attribute_empty_value' => array(
        '<?xml version="1.0"?><config><router id="firsst"><route id="test"><module name="Some_ModuleName" after="" />' .
        '</route></router></config>',
        array(
            "Element 'module', attribute 'after': [facet 'pattern'] The value '' is not accepted " .
            "by the pattern '[A-Za-z0-9_]{3,}'.",
            "Element 'module', attribute 'after': '' is not a valid value of the atomic type 'beforeAfterType'."
        )
    ),
    'route_module_after_attribute_value_regexp1' => array(
        '<?xml version="1.0"?><config><router id="first">'.
        '<route id="test"><module name="Some_ModuleName" after="sd" />' .
        '</route></router></config>',
        array(
            "Element 'module', attribute 'after': [facet 'pattern'] The value 'sd' is not accepted by" .
            " the pattern '[A-Za-z0-9_]{3,}'.",
            "Element 'module', attribute 'after': 'sd' is not a valid value of the atomic type 'beforeAfterType'."
        )
    ),
    'route_module_after_attribute_value_regexp2' => array(
        '<?xml version="1.0"?><config><router id="first"><route id="test">' .
        '<module name="Some_ModuleName" after="!!!!" /></route></router></config>',
        array(
            "Element 'module', attribute 'after': [facet 'pattern'] The value '!!!!' is not accepted by the " .
            "pattern '[A-Za-z0-9_]{3,}'.",
            "Element 'module', attribute 'after': '!!!!' is not a valid value of the atomic type 'beforeAfterType'."
        )
    )
);
