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

/* @var $installer \Magento\Framework\Module\Setup */
$installer = $this;
$connection = $installer->getConnection();

$rulesTable = $installer->getTable('catalogrule');
$websitesTable = $installer->getTable('store_website');
$customerGroupsTable = $installer->getTable('customer_group');
$rulesWebsitesTable = $installer->getTable('catalogrule_website');
$rulesCustomerGroupsTable = $installer->getTable('catalogrule_customer_group');

$installer->startSetup();
/**
 * Create table 'catalogrule_website' if not exists. This table will be used instead of
 * column website_ids of main catalog rules table
 */
if (!$connection->isTableExists($rulesWebsitesTable)) {
    $table = $connection->newTable(
        $rulesWebsitesTable
    )->addColumn(
        'rule_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        array('unsigned' => true, 'nullable' => false, 'primary' => true),
        'Rule Id'
    )->addColumn(
        'website_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        array('unsigned' => true, 'nullable' => false, 'primary' => true),
        'Website Id'
    )->addIndex(
        $installer->getIdxName('catalogrule_website', array('website_id')),
        array('website_id')
    )->addForeignKey(
        $installer->getFkName('catalogrule_website', 'rule_id', 'catalogrule', 'rule_id'),
        'rule_id',
        $rulesTable,
        'rule_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )->addForeignKey(
        $installer->getFkName('catalogrule_website', 'website_id', 'store_website', 'website_id'),
        'website_id',
        $websitesTable,
        'website_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )->setComment(
        'Catalog Rules To Websites Relations'
    );

    $connection->createTable($table);
}

/**
 * Create table 'catalogrule_customer_group' if not exists. This table will be used instead of
 * column customer_group_ids of main catalog rules table
 */
if (!$connection->isTableExists($rulesCustomerGroupsTable)) {
    $table = $connection->newTable(
        $rulesCustomerGroupsTable
    )->addColumn(
        'rule_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        array('unsigned' => true, 'nullable' => false, 'primary' => true),
        'Rule Id'
    )->addColumn(
        'customer_group_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        array('unsigned' => true, 'nullable' => false, 'primary' => true),
        'Customer Group Id'
    )->addIndex(
        $installer->getIdxName('catalogrule_customer_group', array('customer_group_id')),
        array('customer_group_id')
    )->addForeignKey(
        $installer->getFkName('catalogrule_customer_group', 'rule_id', 'catalogrule', 'rule_id'),
        'rule_id',
        $rulesTable,
        'rule_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )->addForeignKey(
        $installer->getFkName(
            'catalogrule_customer_group',
            'customer_group_id',
            'customer_group',
            'customer_group_id'
        ),
        'customer_group_id',
        $customerGroupsTable,
        'customer_group_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )->setComment(
        'Catalog Rules To Customer Groups Relations'
    );

    $connection->createTable($table);
}

$installer->endSetup();
