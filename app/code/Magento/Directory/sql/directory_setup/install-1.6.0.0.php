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
 * Create table 'directory_country'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('directory_country')
)->addColumn(
    'country_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    2,
    array('nullable' => false, 'primary' => true, 'default' => false),
    'Country Id in ISO-2'
)->addColumn(
    'iso2_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    2,
    array('nullable' => true, 'default' => null),
    'Country ISO-2 format'
)->addColumn(
    'iso3_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    3,
    array('nullable' => true, 'default' => null),
    'Country ISO-3'
)->setComment(
    'Directory Country'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'directory_country_format'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('directory_country_format')
)->addColumn(
    'country_format_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Country Format Id'
)->addColumn(
    'country_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    2,
    array('nullable' => true, 'default' => null),
    'Country Id in ISO-2'
)->addColumn(
    'type',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    30,
    array('nullable' => true, 'default' => null),
    'Country Format Type'
)->addColumn(
    'format',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    array('nullable' => false),
    'Country Format'
)->addIndex(
    $installer->getIdxName(
        'directory_country_format',
        array('country_id', 'type'),
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    array('country_id', 'type'),
    array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
)->setComment(
    'Directory Country Format'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'directory_country_region'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('directory_country_region')
)->addColumn(
    'region_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Region Id'
)->addColumn(
    'country_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    4,
    array('nullable' => false, 'default' => '0'),
    'Country Id in ISO-2'
)->addColumn(
    'code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    32,
    array('nullable' => true, 'default' => null),
    'Region code'
)->addColumn(
    'default_name',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    array(),
    'Region Name'
)->addIndex(
    $installer->getIdxName('directory_country_region', array('country_id')),
    array('country_id')
)->setComment(
    'Directory Country Region'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'directory_country_region_name'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('directory_country_region_name')
)->addColumn(
    'locale',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    8,
    array('nullable' => false, 'primary' => true, 'default' => false),
    'Locale'
)->addColumn(
    'region_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'),
    'Region Id'
)->addColumn(
    'name',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    array('nullable' => true, 'default' => null),
    'Region Name'
)->addIndex(
    $installer->getIdxName('directory_country_region_name', array('region_id')),
    array('region_id')
)->addForeignKey(
    $installer->getFkName('directory_country_region_name', 'region_id', 'directory_country_region', 'region_id'),
    'region_id',
    $installer->getTable('directory_country_region'),
    'region_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Directory Country Region Name'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'directory_currency_rate'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('directory_currency_rate')
)->addColumn(
    'currency_from',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    3,
    array('nullable' => false, 'primary' => true, 'default' => false),
    'Currency Code Convert From'
)->addColumn(
    'currency_to',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    3,
    array('nullable' => false, 'primary' => true, 'default' => false),
    'Currency Code Convert To'
)->addColumn(
    'rate',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '24,12',
    array('nullable' => false, 'default' => '0.000000000000'),
    'Currency Conversion Rate'
)->addIndex(
    $installer->getIdxName('directory_currency_rate', array('currency_to')),
    array('currency_to')
)->setComment(
    'Directory Currency Rate'
);
$installer->getConnection()->createTable($table);

$installer->endSetup();
