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
    'types_with_same_name_attribute_value' => array(
        '<?xml version="1.0"?><config><type name="some_name" /><type name="some_name" /></config>',
        array("Element 'type': Duplicate key-sequence ['some_name'] in unique identity-constraint 'uniqueTypeName'.")
    ),
    'type_without_required_name_attribute' => array(
        '<?xml version="1.0"?><config><type /></config>',
        array("Element 'type': The attribute 'name' is required but missing.")
    ),
    'type_with_notallowed_attribute' => array(
        '<?xml version="1.0"?><config><type name="some_name"  notallowed="text"/></config>',
        array("Element 'type', attribute 'notallowed': The attribute 'notallowed' is not allowed.")
    ),
    'type_modelinstance_invalid_value' => array(
        '<?xml version="1.0"?><config><type name="some_name" modelInstance="123" /></config>',
        array(
            "Element 'type', attribute 'modelInstance': [facet 'pattern'] The value '123' is not accepted by the" .
            " pattern '[a-zA-Z_\\\\\\\\]+'.",
            "Element 'type', attribute 'modelInstance': '123' is not a valid value of the atomic type 'modelName'."
        )
    ),
    'type_indexpriority_invalid_value' => array(
        '<?xml version="1.0"?><config><type name="some_name" indexPriority="-1" /></config>',
        array(
            "Element 'type', attribute 'indexPriority': '-1' is not a valid value of the atomic " .
            "type 'xs:nonNegativeInteger'."
        )
    ),
    'type_canuseqtydecimals_invalid_value' => array(
        '<?xml version="1.0"?><config><type name="some_name" canUseQtyDecimals="string" /></config>',
        array(
            "Element 'type', attribute 'canUseQtyDecimals': 'string' is not a valid value of the atomic" .
            " type 'xs:boolean'."
        )
    ),
    'type_isqty_invalid_value' => array(
        '<?xml version="1.0"?><config><type name="some_name" isQty="string" /></config>',
        array("Element 'type', attribute 'isQty': 'string' is not a valid value of the atomic type 'xs:boolean'.")
    ),
    'type_pricemodel_without_required_instance_attribute' => array(
        '<?xml version="1.0"?><config><type name="some_name"><priceModel /></type></config>',
        array("Element 'priceModel': The attribute 'instance' is required but missing.")
    ),
    'type_pricemodel_instance_invalid_value' => array(
        '<?xml version="1.0"?><config><type name="some_name"><priceModel instance="123123" /></type></config>',
        array(
            "Element 'priceModel', attribute 'instance': [facet 'pattern'] The value '123123' is not accepted " .
            "by the pattern '[a-zA-Z_\\\\\\\\]+'.",
            "Element 'priceModel', attribute 'instance': '123123' is not a valid value of the atomic type 'modelName'."
        )
    ),
    'type_indexermodel_instance_invalid_value' => array(
        '<?xml version="1.0"?><config><type name="some_name"><indexerModel instance="123" /></type></config>',
        array(
            "Element 'indexerModel', attribute 'instance': [facet 'pattern'] The value '123' is not accepted by " .
            "the pattern '[a-zA-Z_\\\\\\\\]+'.",
            "Element 'indexerModel', attribute 'instance': '123' is not a valid value of the atomic type 'modelName'."
        )
    ),
    'type_indexermodel_without_required_instance_attribute' => array(
        '<?xml version="1.0"?><config><type name="some_name"><indexerModel /></type></config>',
        array("Element 'indexerModel': The attribute 'instance' is required but missing.")
    ),
    'stockindexermodel_without_required_instance_attribute' => array(
        '<?xml version="1.0"?><config><type name="some_name"><stockIndexerModel /></type></config>',
        array("Element 'stockIndexerModel': The attribute 'instance' is required but missing.")
    ),
    'stockindexermodel_instance_invalid_value' => array(
        '<?xml version="1.0"?><config><type name="some_name"><stockIndexerModel instance="1234"/></type></config>',
        array(
            "Element 'stockIndexerModel', attribute 'instance': [facet 'pattern'] The value '1234' is not " .
            "accepted by the pattern '[a-zA-Z_\\\\\\\\]+'.",
            "Element 'stockIndexerModel', attribute 'instance': '1234' is not a valid value of the atomic " .
            "type 'modelName'."
        )
    ),
    'allowedselectiontypes_without_required_type_handle' => array(
        '<?xml version="1.0"?><config><type name="some_name"><allowedSelectionTypes /></type></config>',
        array("Element 'allowedSelectionTypes': Missing child element(s). Expected is ( type ).")
    ),
    'allowedselectiontypes_type_without_required_name' => array(
        '<?xml version="1.0"?><config><type name="some_name"><allowedSelectionTypes><type/></allowedSelectionTypes>"
        . "</type></config>',
        array(
            "Element 'type': The attribute 'name' is required but missing.",
            "Element 'type': Character content other than whitespace is not allowed because the content " .
            "type is 'element-only'."
        )
    )
);
