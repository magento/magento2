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
    'without_indexer_handle' => array(
        '<?xml version="1.0"?><config></config>',
        array("Element 'config': Missing child element(s). Expected is ( indexer ).")
    ),
    'indexer_with_notallowed_attribute' => array(
        '<?xml version="1.0"?><config>' .
        '<indexer id="somename" view_id="view_01" class="Class\Name" notallowed="some value">' .
        '<title>Test</title><description>Test</description></indexer></config>',
        array("Element 'indexer', attribute 'notallowed': The attribute 'notallowed' is not allowed.")
    ),
    'indexer_without_view_attribute' => array(
        '<?xml version="1.0"?><config><indexer id="somename" class="Class\Name">' .
        '<title>Test</title><description>Test</description></indexer></config>',
        array("Element 'indexer': The attribute 'view_id' is required but missing.")
    ),
    'indexer_duplicate_view_attribute' => array(
        '<?xml version="1.0"?><config><indexer id="somename" view_id="view_01" class="Class\Name">' .
        '<title>Test</title><description>Test</description></indexer>' .
        '<indexer id="somename_two" view_id="view_01" class="Class\Name">' .
        '<title>Test</title><description>Test</description></indexer></config>',
        array("Element 'indexer': Duplicate key-sequence ['view_01'] in unique identity-constraint 'uniqueViewId'.")
    ),
    'indexer_without_title' => array(
        '<?xml version="1.0"?><config><indexer id="somename" view_id="view_01" class="Class\Name">' .
        '<description>Test</description></indexer></config>',
        array("Element 'description': This element is not expected. Expected is ( title ).")
    ),
    'indexer_without_description' => array(
        '<?xml version="1.0"?><config><indexer id="somename" view_id="view_01" class="Class\Name">' .
        '<title>Test</title></indexer></config>',
        array("Element 'indexer': Missing child element(s). Expected is ( description ).")
    )
);
