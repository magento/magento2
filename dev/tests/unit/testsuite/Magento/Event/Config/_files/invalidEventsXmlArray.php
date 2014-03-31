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
    'without_event_handle' => array(
        '<?xml version="1.0"?><config></config>',
        array("Element 'config': Missing child element(s). Expected is ( event ).")
    ),
    'event_without_required_name_attribute' => array(
        '<?xml version="1.0"?><config><event name="some_name"></event></config>',
        array("Element 'event': Missing child element(s). Expected is ( observer ).")
    ),
    'event_with_notallowed_attribute' => array(
        '<?xml version="1.0"?><config><event name="somename" notallowed="some value"><observer name="observer_name" ' .
        '/></event></config>',
        array("Element 'event', attribute 'notallowed': The attribute 'notallowed' is not allowed.")
    ),
    'event_with_same_name_attribute_value' => array(
        '<?xml version="1.0"?><config><event name="same_name"><observer name="observer_name" /></event><event ' .
        'name="same_name"><observer name="observer_name" /></event></config>',
        array(
            "Element 'event': Duplicate key-sequence ['same_name'] in unique identity-constraint " .
            "'uniqueEventName'."
        )
    ),
    'event_without_required_observer_handle' => array(
        '<?xml version="1.0"?><config><event name="some_name"></event></config>',
        array("Element 'event': Missing child element(s). Expected is ( observer ).")
    ),
    'event_without_required_observer_name_attribute' => array(
        '<?xml version="1.0"?><config><event name="some_name"><observer shared="true"/></event></config>',
        array("Element 'observer': The attribute 'name' is required but missing.")
    ),
    'event_with_same_observer_handle_name' => array(
        '<?xml version="1.0"?><config><event name="some_name"><observer  name="observer_name"/><observer  ' .
        'name="observer_name"/></event></config>',
        array(
            "Element 'observer': Duplicate key-sequence ['observer_name'] in unique identity-constraint " .
            "'uniqueObserverName'."
        )
    ),
    'event_observer_with_invalid_disabled_value' => array(
        '<?xml version="1.0"?><config><event name="some_name"><observer ' .
        'name="observer_name" disabled="string"/></event></config>',
        array(
            "Element 'observer', attribute 'disabled': 'string' is not a valid value of the atomic type" .
            " 'xs:boolean'."
        )
    ),
    'event_observer_with_invalid_shared_value' => array(
        '<?xml version="1.0"?><config><event name="some_name"><observer ' .
        'name="observer_name" shared="string"/></event></config>',
        array(
            "Element 'observer', attribute 'shared': 'string' is not a valid value of the atomic type" .
            " 'xs:boolean'."
        )
    ),
    'event_observer_with_invalid_method_value' => array(
        '<?xml version="1.0"?><config><event name="some_name"><observer ' .
        'name="observer_name" method="_wrong name"/></event></config>',
        array(
            "Element 'observer', attribute 'method': [facet 'pattern'] The value '_wrong name' is not accepted by" .
            " the pattern '[a-zA-Z]+'.",
            "Element 'observer', attribute 'method': '_wrong name' is not a valid value of the atomic type " .
            "'methodName'."
        )
    )
);
