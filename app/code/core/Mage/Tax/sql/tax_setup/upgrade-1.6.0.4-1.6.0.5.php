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
 * @category    Mage
 * @package     Mage_Tax
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/** @var $installer Mage_Tax_Model_Resource_Setup */
$installer = $this;

$connection = $installer->getConnection();
$adminRuleTable = $installer->getTable('admin_rule');
$aclRulesDelete = array(
    'Mage_Tax::classes_customer',
    'Mage_Tax::classes_product',
    'Mage_Tax::import_export',
    'Mage_Tax::tax_rates',
    'Mage_Tax::rules'
);

/**
 * Remove unneeded ACL rules
 */
$connection->delete(
    $adminRuleTable,
    $connection->quoteInto('resource_id IN (?)', $aclRulesDelete)
);

$connection->update(
    $adminRuleTable,
    array('resource_id' => 'Mage_Tax::manage_tax'),
    array('resource_id = ?' => 'Mage_Tax::sales_tax')
);
