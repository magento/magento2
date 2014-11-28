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

$installer = $this;
/* @var $installer \Magento\Framework\Module\Setup */

$installer->startSetup();

/**
 * Create table 'indexer_state'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('indexer_state'))
    ->addColumn(
        'state_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
        'Indexer State Id'
    )
    ->addColumn(
        'indexer_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        255,
        [],
        'Indexer Id'
    )
    ->addColumn(
        'status',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        16,
        ['default' => 'invalid'],
        'Indexer Status'
    )
    ->addColumn(
        'updated',
        \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
        null,
        [],
        'Indexer Status'
    )
    ->addIndex(
        $installer->getIdxName('indexer_state', ['indexer_id']),
        ['indexer_id']
    )
    ->setComment('Indexer State');
$installer->getConnection()->createTable($table);

/**
 * Create table 'mview_state'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('mview_state'))
    ->addColumn(
        'state_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        [
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ],
        'View State Id'
    )
    ->addColumn(
        'view_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        255,
        [],
        'View Id'
    )
    ->addColumn(
        'mode',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        16,
        ['default' => 'disabled'],
        'View Mode'
    )
    ->addColumn(
        'status',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        16,
        ['default' => 'idle'],
        'View Status'
    )
    ->addColumn(
        'updated',
        \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
        null,
        [],
        'View updated time'
    )
    ->addColumn(
        'version_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true],
        'View Version Id'
    )
    ->addIndex(
        $installer->getIdxName('mview_state', ['view_id']),
        ['view_id']
    )
    ->addIndex(
        $installer->getIdxName('mview_state', ['mode']),
        ['mode']
    )
    ->setComment('View State');
$installer->getConnection()->createTable($table);

$installer->endSetup();
