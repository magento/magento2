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
 * @package     Magento_Reports
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/** @var $this \Magento\Core\Model\Resource\Setup */

/**
 * Create table 'report_compared_product_index'.
 * In MySQL version this table comes with unique keys to implement insertOnDuplicate(), so that
 * only one record is added when customer/visitor compares same product again.
 */
$table = $this->getConnection()->newTable(
    $this->getTable('report_compared_product_index')
)->addColumn(
    'index_id',
    \Magento\DB\Ddl\Table::TYPE_BIGINT,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Index Id'
)->addColumn(
    'visitor_id',
    \Magento\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true),
    'Visitor Id'
)->addColumn(
    'customer_id',
    \Magento\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true),
    'Customer Id'
)->addColumn(
    'product_id',
    \Magento\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false),
    'Product Id'
)->addColumn(
    'store_id',
    \Magento\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true),
    'Store Id'
)->addColumn(
    'added_at',
    \Magento\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    array('nullable' => false),
    'Added At'
)->addIndex(
    $this->getIdxName(
        'report_compared_product_index',
        array('visitor_id', 'product_id'),
        \Magento\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    array('visitor_id', 'product_id'),
    array('type' => \Magento\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
)->addIndex(
    $this->getIdxName(
        'report_compared_product_index',
        array('customer_id', 'product_id'),
        \Magento\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    array('customer_id', 'product_id'),
    array('type' => \Magento\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
)->addIndex(
    $this->getIdxName('report_compared_product_index', array('store_id')),
    array('store_id')
)->addIndex(
    $this->getIdxName('report_compared_product_index', array('added_at')),
    array('added_at')
)->addIndex(
    $this->getIdxName('report_compared_product_index', array('product_id')),
    array('product_id')
)->addForeignKey(
    $this->getFkName('report_compared_product_index', 'customer_id', 'customer_entity', 'entity_id'),
    'customer_id',
    $this->getTable('customer_entity'),
    'entity_id',
    \Magento\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $this->getFkName('report_compared_product_index', 'product_id', 'catalog_product_entity', 'entity_id'),
    'product_id',
    $this->getTable('catalog_product_entity'),
    'entity_id',
    \Magento\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $this->getFkName('report_compared_product_index', 'store_id', 'core_store', 'store_id'),
    'store_id',
    $this->getTable('core_store'),
    'store_id',
    \Magento\DB\Ddl\Table::ACTION_SET_NULL,
    \Magento\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Reports Compared Product Index Table'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'report_viewed_product_index'
 * In MySQL version this table comes with unique keys to implement insertOnDuplicate(), so that
 * only one record is added when customer/visitor views same product again.
 */
$table = $this->getConnection()->newTable(
    $this->getTable('report_viewed_product_index')
)->addColumn(
    'index_id',
    \Magento\DB\Ddl\Table::TYPE_BIGINT,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Index Id'
)->addColumn(
    'visitor_id',
    \Magento\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true),
    'Visitor Id'
)->addColumn(
    'customer_id',
    \Magento\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true),
    'Customer Id'
)->addColumn(
    'product_id',
    \Magento\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false),
    'Product Id'
)->addColumn(
    'store_id',
    \Magento\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true),
    'Store Id'
)->addColumn(
    'added_at',
    \Magento\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    array('nullable' => false),
    'Added At'
)->addIndex(
    $this->getIdxName(
        'report_viewed_product_index',
        array('visitor_id', 'product_id'),
        \Magento\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    array('visitor_id', 'product_id'),
    array('type' => \Magento\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
)->addIndex(
    $this->getIdxName(
        'report_viewed_product_index',
        array('customer_id', 'product_id'),
        \Magento\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    array('customer_id', 'product_id'),
    array('type' => \Magento\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
)->addIndex(
    $this->getIdxName('report_viewed_product_index', array('store_id')),
    array('store_id')
)->addIndex(
    $this->getIdxName('report_viewed_product_index', array('added_at')),
    array('added_at')
)->addIndex(
    $this->getIdxName('report_viewed_product_index', array('product_id')),
    array('product_id')
)->addForeignKey(
    $this->getFkName('report_viewed_product_index', 'customer_id', 'customer_entity', 'entity_id'),
    'customer_id',
    $this->getTable('customer_entity'),
    'entity_id',
    \Magento\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $this->getFkName('report_viewed_product_index', 'product_id', 'catalog_product_entity', 'entity_id'),
    'product_id',
    $this->getTable('catalog_product_entity'),
    'entity_id',
    \Magento\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $this->getFkName('report_viewed_product_index', 'store_id', 'core_store', 'store_id'),
    'store_id',
    $this->getTable('core_store'),
    'store_id',
    \Magento\DB\Ddl\Table::ACTION_SET_NULL,
    \Magento\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Reports Viewed Product Index Table'
);
$this->getConnection()->createTable($table);

$installFile = __DIR__ . '/install-1.6.0.0.php';

/** @var \Magento\Filesystem\Directory\Read $modulesDirectory */
$modulesDirectory = $this->getFilesystem()->getDirectoryRead(\Magento\App\Filesystem::MODULES_DIR);
if ($modulesDirectory->isExist($modulesDirectory->getRelativePath($installFile))) {
    include $installFile;
}
