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
 * @package     Mage_GoogleOptimizer
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * GoogleOptimizer install
 *
 * @category    Mage
 * @package     Mage_GoogleOptimizer
 * @author      Magento Core Team <core@magentocommerce.com>
 */
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

/**
 * Create table 'googleoptimizer_code'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('googleoptimizer_code'))
    ->addColumn('code_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Google optimizer code id')
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Optimized entity id product id or catalog id')
    ->addColumn('entity_type', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        ), 'Optimized entity type')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Store id')
    ->addColumn('control_script', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Google optimizer control script')
    ->addColumn('tracking_script', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Google optimizer tracking script')
    ->addColumn('conversion_script', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Google optimizer conversion script')
    ->addColumn('conversion_page', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Google optimizer conversion page')
    ->addColumn('additional_data', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Google optimizer additional data')
    ->addIndex($installer->getIdxName('googleoptimizer_code', array('store_id')),
        array('store_id'))
    ->addForeignKey($installer->getFkName('googleoptimizer_code', 'store_id', 'core_store', 'store_id'),
        'store_id', $installer->getTable('core_store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Googleoptimizer code');
$installer->getConnection()->createTable($table);

$installer->endSetup();
