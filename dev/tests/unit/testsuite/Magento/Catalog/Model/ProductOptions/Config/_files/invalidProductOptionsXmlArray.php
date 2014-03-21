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
    'options_node_is_required' => array(
        '<?xml version="1.0"?><config><inputType name="name_one" /></config>',
        array("Element 'inputType': This element is not expected. Expected is ( option ).")
    ),
    'inputType_node_is_required' => array(
        '<?xml version="1.0"?><config><option name="name_one"/></config>',
        array("Element 'option': Missing child element(s). Expected is ( inputType ).")
    ),
    'options_name_must_be_unique' => array(
        '<?xml version="1.0"?><config><option name="name_one"><inputType name="name"/>' .
        '</option><option name="name_one"><inputType name="name_two"/></option></config>',
        array(
            "Element 'option': Duplicate key-sequence ['name_one'] in unique identity-constraint " .
            "'uniqueOptionName'."
        )
    ),
    'inputType_name_must_be_unique' => array(
        '<?xml version="1.0"?><config><option name="name"><inputType name="name_one"/>' .
        '<inputType name="name_one"/></option></config>',
        array(
            "Element 'inputType': Duplicate key-sequence ['name_one'] in unique identity-constraint " .
            "'uniqueInputTypeName'."
        )
    ),
    'renderer_attribute_with_invalid_value' => array(
        '<?xml version="1.0"?><config><option name="name_one" renderer="true12"><inputType name="name_one"/>' .
        '</option></config>',
        array(
            "Element 'option', attribute 'renderer': [facet 'pattern'] The value 'true12' is not accepted by the " .
            "pattern '[a-zA-Z_\\\\\\\\]+'.",
            "Element 'option', attribute 'renderer': 'true12' is not a valid value of the atomic" .
            " type 'modelName'."
        )
    ),
    'disabled_attribute_with_invalid_value' => array(
        '<?xml version="1.0"?><config><option name="name_one"><inputType name="name_one" disabled="7"/>' .
        '<inputType name="name_two" disabled="some_string"/></option></config>',
        array(
            "Element 'inputType', attribute 'disabled': '7' is not a valid value of the atomic type 'xs:boolean'.",
            "Element 'inputType', attribute 'disabled': 'some_string' is not a valid value of the atomic type " .
            "'xs:boolean'."
        )
    )
);
