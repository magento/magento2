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
    '$replaceRules' => array(
        array(
            'table',
            'field',
            \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_MODEL,
            \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_PLAIN
        )
    ),
    '$tableData' => array(
        array('field' => 'customer/customer'),
        array('field' => 'customer/attribute_data_postcode'),
        array('field' => 'customer/attribute_data_postcode::someMethod'),
        array('field' => 'Magento\Customer\Model\Customer')
    ),
    '$expected' => array(
        'updates' => array(
            array(
                'table' => 'table',
                'field' => 'field',
                'to' => 'Magento\Customer\Model\Customer_FROM_MAP',
                'from' => array('`field` = ?' => 'customer/customer')
            ),
            array(
                'table' => 'table',
                'field' => 'field',
                'to' => 'Magento\Customer\Model\Attribute\Data\Postcode',
                'from' => array('`field` = ?' => 'customer/attribute_data_postcode')
            ),
            array(
                'table' => 'table',
                'field' => 'field',
                'to' => 'Magento\Customer\Model\Attribute\Data\Postcode::someMethod',
                'from' => array('`field` = ?' => 'customer/attribute_data_postcode::someMethod')
            )
        ),
        'aliases_map' => array(
            \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_MODEL => array(
                'customer/customer' => 'Magento\Customer\Model\Customer_FROM_MAP',
                'customer/attribute_data_postcode' => 'Magento\Customer\Model\Attribute\Data\Postcode'
            )
        )
    ),
    '$aliasesMap' => array(
        \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_MODEL => array(
            'customer/customer' => 'Magento\Customer\Model\Customer_FROM_MAP'
        )
    )
);
