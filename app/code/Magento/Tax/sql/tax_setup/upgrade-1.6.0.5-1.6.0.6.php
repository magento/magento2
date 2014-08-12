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
 * @category    Magento
 * @package     Magento_Tax
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/** @var $installer \Magento\Tax\Model\Resource\Setup */
$installer = $this;

$connection = $installer->getConnection();
$adminRuleTable = $installer->getTable('authorization_rule');
$aclRulesDelete = array(
    'Magento_Tax::classes_customer',
    'Magento_Tax::classes_product',
    'Magento_Tax::import_export',
    'Magento_Tax::tax_rates',
    'Magento_Tax::rules'
);

/**
 * Remove unneeded ACL rules
 */
$connection->delete($adminRuleTable, $connection->quoteInto('resource_id IN (?)', $aclRulesDelete));

$connection->update(
    $adminRuleTable,
    array('resource_id' => 'Magento_Tax::manage_tax'),
    array('resource_id = ?' => 'Magento_Tax::sales_tax')
);

/**
 * Add new field to 'tax_calculation_rule'
 */
$connection->addColumn(
    $this->getTable('tax_calculation_rule'),
    'calculate_subtotal',
    [
        'TYPE' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        'NULLABLE' => false,
        'COMMENT' => 'Calculate off subtotal option',
    ]
);
