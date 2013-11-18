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
 * @package     Magento_GoogleCheckout
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/** @var $installer \Magento\Catalog\Model\Resource\Setup */
$installer = $this;

/**
 * Prepare database for tables setup
 */
$installer->startSetup();

/**
 * Create table 'googlecheckout_notification'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('googlecheckout_notification'))
    ->addColumn('serial_number', \Magento\DB\Ddl\Table::TYPE_TEXT, 64, array(
        'nullable'  => false,
        'primary'   => true,
        ), 'Serial Number')
    ->addColumn('started_at', \Magento\DB\Ddl\Table::TYPE_TIMESTAMP, null, array(
        ), 'Started At')
    ->addColumn('status', \Magento\DB\Ddl\Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Status')
    ->setComment('Google Checkout Notification Table');
$installer->getConnection()->createTable($table);

/**
 * Add 'disable_googlecheckout' attribute to the 'eav_attribute' table
 */
$installer->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'enable_googlecheckout', array(
    'group'             => 'Prices',
    'type'              => 'int',
    'backend'           => '',
    'frontend'          => '',
    'label'             => 'Is Product Available for Purchase with Google Checkout',
    'input'             => 'select',
    'class'             => '',
    'source'            => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
    'global'            => \Magento\Catalog\Model\Resource\Eav\Attribute::SCOPE_GLOBAL,
    'visible'           => true,
    'required'          => false,
    'user_defined'      => false,
    'default'           => '1',
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'unique'            => false,
    'apply_to'          => '',
    'is_configurable'   => false
));

/**
 * Prepare database after tables setup
 */
$installer->endSetup();
