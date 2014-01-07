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
    'indexer_node_is_required' => array(
        '<?xml version="1.0"?><config><depends name="name one"/></config>',
        array("Element 'depends': This element is not expected. Expected is ( indexer ).")),
    'indexer_name_must_be_unique' => array(
        '<?xml version="1.0"?><config><indexer name="name" instance="instance" />'
        . '<indexer name="name" instance="instance" /></config>',
        array("Element 'indexer': Duplicate key-sequence ['name'] in unique identity-constraint 'uniqueIndexerName'.")),
    'depends_name_must_be_unique' => array(
        '<?xml version="1.0"?><config><indexer name="name" instance="instance"><depends name="name" />'
        . '<depends name="name" /></indexer></config>',
        array("Element 'depends': Duplicate key-sequence ['name'] in unique identity-constraint 'uniqueDependsName'.")),
    'indexer_node_without_required_attribute' => array(
        '<?xml version="1.0"?><config><indexer name="name"/><indexer instance="instance" /></config>',
        array("Element 'indexer': The attribute 'name' is required but missing.")),
    'depends_without_required_attribute' => array(
        '<?xml version="1.0"?><config><indexer name="name" instance="instance"><depends/></indexer></config>',
        array("Element 'depends': The attribute 'name' is required but missing.")),
    'name_attribute_with_invalid_value' => array(
        '<?xml version="1.0"?><config><indexer name="name" instance="instance"><depends name="Name" />'
        . '<depends name="name12" /></indexer></config>',
        array("Element 'depends', attribute 'name': [facet 'pattern'] The value 'Name' is not accepted by the pattern "
        . "'[a-z_]+'.", "Element 'depends', attribute 'name': 'Name' is not a valid value of the atomic type "
        . "'identifierType'.", "Element 'depends', attribute 'name': Warning: No precomputed value available, the value"
        . " was either invalid or something strange happend.", "Element 'depends', attribute 'name': [facet 'pattern'] "
        . "The value 'name12' is not accepted by the pattern '[a-z_]+'.", "Element 'depends', attribute 'name': "
        . "'name12' is not a valid value of the atomic type 'identifierType'.", "Element 'depends', attribute 'name': "
        . "Warning: No precomputed value available, the value was either invalid or something strange happend.")),
    'instance_attribute_with_invalid_value' => array(
        '<?xml version="1.0"?><config><indexer name="name" instance="10"/><indexer name="name_one" '
        . 'instance="One_Two1" /></config>',
        array("Element 'indexer', attribute 'instance': [facet 'pattern'] The value '10' is not accepted by the pattern"
        . " '[a-zA-Z_\\\\\\\\]+'.",
              "Element 'indexer', attribute 'instance': '10' is not a valid value of the atomic type "
        . "'instanceType'.", "Element 'indexer', attribute 'instance': [facet 'pattern'] The value 'One_Two1' is not "
        . "accepted by the pattern '[a-zA-Z_\\\\\\\\]+'.",
              "Element 'indexer', attribute 'instance': 'One_Two1' is not a valid "
        . "value of the atomic type 'instanceType'.")),
);
