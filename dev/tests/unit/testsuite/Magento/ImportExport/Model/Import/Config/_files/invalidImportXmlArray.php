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
    'entity_same_name_attribute_value' => array(
        '<?xml version="1.0"?><config><entity name="same_name"/><entity name="same_name"/></config>',
        array(
            "Element 'entity': Duplicate key-sequence ['same_name'] in unique " .
            "identity-constraint 'uniqueEntityName'."
        )
    ),
    'entity_without_required_name_attribute' => array(
        '<?xml version="1.0"?><config><entity /></config>',
        array("Element 'entity': The attribute 'name' is required but missing.")
    ),
    'entity_with_invalid_model_value' => array(
        '<?xml version="1.0"?><config><entity name="some_name" model="12345"/></config>',
        array(
            "Element 'entity', attribute 'model': [facet 'pattern'] The value '12345' is not accepted by " .
            "the pattern '[A-Za-z_\\\\\\\\]+'.",
            "Element 'entity', attribute 'model': '12345' is not a valid value of the atomic type 'modelName'."
        )
    ),
    'entity_with_invalid_behaviormodel_value' => array(
        '<?xml version="1.0"?><config><entity name="some_name" behaviorModel="=--09"/></config>',
        array(
            "Element 'entity', attribute 'behaviorModel': [facet 'pattern'] The value '=--09' is not " .
            "accepted by the pattern '[A-Za-z_\\\\\\\\]+'.",
            "Element 'entity', attribute 'behaviorModel': '=--09' is not a valid value of the atomic type 'modelName'."
        )
    ),
    'entity_with_notallowed_attribute' => array(
        '<?xml version="1.0"?><config><entity name="some_name" notallowd="aasd"/></config>',
        array("Element 'entity', attribute 'notallowd': The attribute 'notallowd' is not allowed.")
    ),
    'entitytype_without_required_name_attribute' => array(
        '<?xml version="1.0"?><config><entityType entity="entity_name" model="model_name" /></config>',
        array("Element 'entityType': The attribute 'name' is required but missing.")
    ),
    'entitytype_without_required_model_attribute' => array(
        '<?xml version="1.0"?><config><entityType entity="entity_name" name="some_name" /></config>',
        array("Element 'entityType': The attribute 'model' is required but missing.")
    ),
    'entitytype_with_invalid_model_attribute_value' => array(
        '<?xml version="1.0"?><config><entityType entity="entity_name" name="some_name" model="test1"/></config>',
        array(
            "Element 'entityType', attribute 'model': [facet 'pattern'] The value 'test1' is not " .
            "accepted by the pattern '[A-Za-z_\\\\\\\\]+'.",
            "Element 'entityType', attribute 'model': 'test1' is not a valid value of the atomic type 'modelName'."
        )
    ),
    'entitytype_with_notallowed' => array(
        '<?xml version="1.0"?><config><entityType entity="entity_name" name="some_name" '
            . 'model="test" notallowed="test"/></config>',
        array("Element 'entityType', attribute 'notallowed': The attribute 'notallowed' is not allowed.")
    )
);
