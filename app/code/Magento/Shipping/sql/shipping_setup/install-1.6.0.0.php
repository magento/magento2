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
 * @package     Magento_Shipping
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$installer = $this;
/** @var $installer \Magento\Core\Model\Resource\Setup */

$installer->startSetup();

/**
 * Create table 'shipping_tablerate'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('shipping_tablerate'))
    ->addColumn('pk', \Magento\DB\Ddl\Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Primary key')
    ->addColumn('website_id', \Magento\DB\Ddl\Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Website Id')
    ->addColumn('dest_country_id', \Magento\DB\Ddl\Table::TYPE_TEXT, 4, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Destination coutry ISO/2 or ISO/3 code')
    ->addColumn('dest_region_id', \Magento\DB\Ddl\Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Destination Region Id')
    ->addColumn('dest_zip', \Magento\DB\Ddl\Table::TYPE_TEXT, 10, array(
        'nullable'  => false,
        'default'   => '*',
        ), 'Destination Post Code (Zip)')
    ->addColumn('condition_name', \Magento\DB\Ddl\Table::TYPE_TEXT, 20, array(
        'nullable'  => false,
        ), 'Rate Condition name')
    ->addColumn('condition_value', \Magento\DB\Ddl\Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Rate condition value')
    ->addColumn('price', \Magento\DB\Ddl\Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Price')
    ->addColumn('cost', \Magento\DB\Ddl\Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Cost')
    ->addIndex($installer->getIdxName('shipping_tablerate', array('website_id', 'dest_country_id', 'dest_region_id', 'dest_zip', 'condition_name', 'condition_value'), \Magento\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE),
        array('website_id', 'dest_country_id', 'dest_region_id', 'dest_zip', 'condition_name', 'condition_value'), array('type' => \Magento\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE))
    ->setComment('Shipping Tablerate');
$installer->getConnection()->createTable($table);

$installer->endSetup();
