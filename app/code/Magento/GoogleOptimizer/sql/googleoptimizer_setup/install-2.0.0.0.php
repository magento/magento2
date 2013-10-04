<?php
/**
 * GoogleOptimizer install
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
$installer = $this;
/* @var $installer \Magento\Core\Model\Resource\Setup */

$installer->startSetup();

/**
 * Create table 'googleoptimizer_code'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('googleoptimizer_code'))
    ->addColumn('code_id', \Magento\DB\Ddl\Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Google experiment code id')
    ->addColumn('entity_id', \Magento\DB\Ddl\Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Optimized entity id product id or catalog id')
    ->addColumn('entity_type', \Magento\DB\Ddl\Table::TYPE_TEXT, 50, array(
        ), 'Optimized entity type')
    ->addColumn('store_id', \Magento\DB\Ddl\Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Store id')
    ->addColumn('experiment_script', \Magento\DB\Ddl\Table::TYPE_TEXT, '64k', array(), 'Google experiment script')
    ->addIndex($installer->getIdxName('googleoptimizer_code', array('store_id')), array('store_id'))
    ->addIndex($installer->getIdxName(
        'googleoptimizer_code',
        array('store_id', 'entity_id', 'entity_type'),
        \Magento\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ), array('store_id', 'entity_id', 'entity_type'), array('type' => \Magento\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE))
    ->addForeignKey($installer->getFkName('googleoptimizer_code', 'store_id', 'core_store', 'store_id'),
        'store_id', $installer->getTable('core_store'), 'store_id',
        \Magento\DB\Ddl\Table::ACTION_CASCADE, \Magento\DB\Ddl\Table::ACTION_CASCADE)
    ->setComment('Google Experiment code');
$installer->getConnection()->createTable($table);

$installer->endSetup();
