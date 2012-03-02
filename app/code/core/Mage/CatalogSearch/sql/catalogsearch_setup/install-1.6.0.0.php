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
 * @package     Mage_CatalogSearch
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

/**
 * Create table 'catalogsearch_query'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('catalogsearch_query'))
    ->addColumn('query_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Query ID')
    ->addColumn('query_text', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Query text')
    ->addColumn('num_results', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Num results')
    ->addColumn('popularity', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Popularity')
    ->addColumn('redirect', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Redirect')
    ->addColumn('synonym_for', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Synonym for')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Store ID')
    ->addColumn('display_in_terms', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'default'   => '1',
        ), 'Display in terms')
    ->addColumn('is_active', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'default'   => '1',
        ), 'Active status')
    ->addColumn('is_processed', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'default'   => '0',
        ), 'Processed status')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Updated at')
    ->addIndex($installer->getIdxName('catalogsearch_query', array('query_text','store_id','popularity')),
        array('query_text','store_id','popularity'))
    ->addIndex($installer->getIdxName('catalogsearch_query', 'store_id'), 'store_id')
    ->addForeignKey($installer->getFkName('catalogsearch_query', 'store_id', 'core_store', 'store_id'),
        'store_id', $installer->getTable('core_store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Catalog search query table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalogsearch_result'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('catalogsearch_result'))
    ->addColumn('query_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Query ID')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Product ID')
    ->addColumn('relevance', Varien_Db_Ddl_Table::TYPE_DECIMAL, '20,4', array(
        'nullable'  => false,
        'default'   => '0.0000'
        ), 'Relevance')
    ->addIndex($installer->getIdxName('catalogsearch_result', 'query_id'), 'query_id')
    ->addForeignKey($installer->getFkName('catalogsearch_result', 'query_id', 'catalogsearch_query', 'query_id'),
        'query_id', $installer->getTable('catalogsearch_query'), 'query_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addIndex($installer->getIdxName('catalogsearch_result', 'product_id'), 'product_id')
    ->addForeignKey($installer->getFkName('catalogsearch_result', 'product_id', 'catalog_product_entity', 'entity_id'),
        'product_id', $installer->getTable('catalog_product_entity'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Catalog search result table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalogsearch_fulltext'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('catalogsearch_fulltext'))
    ->addColumn('fulltext_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Entity ID')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Product ID')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Store ID')
    ->addColumn('data_index', Varien_Db_Ddl_Table::TYPE_TEXT, '4g', array(
        ), 'Data index')
    ->addIndex(
        $installer->getIdxName(
            'catalogsearch_fulltext',
            array('product_id', 'store_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('product_id', 'store_id'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex(
        $installer->getIdxName(
            'catalogsearch_fulltext',
            'data_index',
            Varien_Db_Adapter_Interface::INDEX_TYPE_FULLTEXT
         ),
        'data_index',
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_FULLTEXT))
    ->setOption('type', 'MyISAM')
    ->setComment('Catalog search result table');
$installer->getConnection()->createTable($table);

$installer->endSetup();
