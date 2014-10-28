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
        '<?xml version="1.0"?><config><inputType name="name_one" label="Label One"/></config>',
        array("Element 'inputType': This element is not expected. Expected is ( option ).")
    ),
    'inputType_node_is_required' => array(
        '<?xml version="1.0"?><config><option name="name_one" label="Label One" renderer="one"/></config>',
        array("Element 'option': Missing child element(s). Expected is ( inputType ).")
    ),
    'options_node_without_required_attributes' => array(
        '<?xml version="1.0"?><config><option name="name_one" label="label one"><inputType name="name" label="one"/>' .
        '</option><option name="name_two" renderer="renderer"><inputType name="name_two" label="one" /></option>' .
        '<option label="label three" renderer="renderer"><inputType name="name_one" label="one"/></option></config>',
        array(
            "Element 'option': The attribute 'renderer' is required but missing.",
            "Element 'option': The attribute " . "'label' is required but missing.",
            "Element 'option': The attribute 'name' is required but missing."
        )
    ),
    'inputType_node_without_required_attributes' => array(
        '<?xml version="1.0"?><config><option name="name_one" label="label one" renderer="renderer">' .
        '<inputType name="name_one"/></option><option name="name_two" renderer="renderer" label="label">' .
        '<inputType label="name_two"/></option></config>',
        array(
            "Element 'inputType': The attribute 'label' is required but missing.",
            "Element 'inputType': The " . "attribute 'name' is required but missing."
        )
    )
);
