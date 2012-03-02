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
 * @package     Mage_Downloadable
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/** @var $installer Mage_Catalog_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/**
 * Create table 'downloadable_link'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('downloadable_link'))
    ->addColumn('link_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Link ID')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Product ID')
    ->addColumn('sort_order', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Sort order')
    ->addColumn('number_of_downloads', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => true,
        ), 'Number of downloads')
    ->addColumn('is_shareable', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Shareable flag')
    ->addColumn('link_url', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Link Url')
    ->addColumn('link_file', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Link File')
    ->addColumn('link_type', Varien_Db_Ddl_Table::TYPE_TEXT, 20, array(
        ), 'Link Type')
    ->addColumn('sample_url', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Sample Url')
    ->addColumn('sample_file', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Sample File')
    ->addColumn('sample_type', Varien_Db_Ddl_Table::TYPE_TEXT, 20, array(
        ), 'Sample Type')
    ->addIndex($installer->getIdxName('downloadable_link', 'product_id'), 'product_id')
    ->addIndex($installer->getIdxName('downloadable_link', array('product_id','sort_order')),
        array('product_id','sort_order'))
    ->addForeignKey($installer->getFkName('downloadable_link', 'product_id', 'catalog_product_entity', 'entity_id'),
        'product_id', $installer->getTable('catalog_product_entity'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Downloadable Link Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'downloadable_link_price'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('downloadable_link_price'))
    ->addColumn('price_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Price ID')
    ->addColumn('link_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Link ID')
    ->addColumn('website_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Website ID')
    ->addColumn('price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Price')
    ->addIndex($installer->getIdxName('downloadable_link_price', 'link_id'), 'link_id')
    ->addForeignKey($installer->getFkName('downloadable_link_price', 'link_id', 'downloadable_link', 'link_id'),
        'link_id', $installer->getTable('downloadable_link'), 'link_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addIndex($installer->getIdxName('downloadable_link_price', 'website_id'), 'website_id')
    ->addForeignKey($installer->getFkName('downloadable_link_price', 'website_id', 'core_website', 'website_id'),
        'website_id', $installer->getTable('core_website'), 'website_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Downloadable Link Price Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'downloadable_link_purchased'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('downloadable_link_purchased'))
    ->addColumn('purchased_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Purchased ID')
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'default'   => '0',
        ), 'Order ID')
    ->addColumn('order_increment_id', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        ), 'Order Increment ID')
    ->addColumn('order_item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Order Item ID')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Date of creation')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Date of modification')
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => true,
        'default'   => '0',
        ), 'Customer ID')
    ->addColumn('product_name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Product name')
    ->addColumn('product_sku', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Product sku')
    ->addColumn('link_section_title', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Link_section_title')
    ->addIndex($installer->getIdxName('downloadable_link_purchased', 'order_id'), 'order_id')
    ->addIndex($installer->getIdxName('downloadable_link_purchased', 'order_item_id'), 'order_item_id')
    ->addIndex($installer->getIdxName('downloadable_link_purchased', 'customer_id'), 'customer_id')
    ->addForeignKey($installer->getFkName('downloadable_link_purchased', 'customer_id', 'customer_entity', 'entity_id'),
        'customer_id', $installer->getTable('customer_entity'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('downloadable_link_purchased', 'order_id', 'sales_flat_order', 'entity_id'),
        'order_id', $installer->getTable('sales_flat_order'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Downloadable Link Purchased Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'downloadable_link_purchased_item'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('downloadable_link_purchased_item'))
    ->addColumn('item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Item ID')
    ->addColumn('purchased_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Purchased ID')
    ->addColumn('order_item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'default'   => '0',
        ), 'Order Item ID')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => true,
        'default'   => '0',
        ), 'Product ID')
    ->addColumn('link_hash', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Link hash')
    ->addColumn('number_of_downloads_bought', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Number of downloads bought')
    ->addColumn('number_of_downloads_used', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Number of downloads used')
    ->addColumn('link_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Link ID')
    ->addColumn('link_title', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Link Title')
    ->addColumn('is_shareable', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Shareable Flag')
    ->addColumn('link_url', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Link Url')
    ->addColumn('link_file', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Link File')
    ->addColumn('link_type', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Link Type')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        ), 'Status')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Creation Time')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Update Time')
    ->addIndex($installer->getIdxName('downloadable_link_purchased_item', 'link_hash'), 'link_hash')
    ->addIndex($installer->getIdxName('downloadable_link_purchased_item', 'order_item_id'), 'order_item_id')
    ->addIndex($installer->getIdxName('downloadable_link_purchased_item', 'purchased_id'), 'purchased_id')
    ->addForeignKey(
        $installer->getFkName(
            'downloadable_link_purchased_item',
            'purchased_id',
            'downloadable_link_purchased',
            'purchased_id'
        ),
        'purchased_id', $installer->getTable('downloadable_link_purchased'), 'purchased_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(
            'downloadable_link_purchased_item',
            'order_item_id',
            'sales_flat_order_item',
            'item_id'
        ),
        'order_item_id', $installer->getTable('sales_flat_order_item'), 'item_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Downloadable Link Purchased Item Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'downloadable_link_title'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('downloadable_link_title'))
    ->addColumn('title_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Title ID')
    ->addColumn('link_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Link ID')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Store ID')
    ->addColumn('title', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Title')
    ->addIndex(
        $installer->getIdxName(
            'downloadable_link_title',
            array('link_id', 'store_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('link_id', 'store_id'),
        array('type'=>Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName('downloadable_link_title', 'link_id'), 'link_id')
    ->addForeignKey($installer->getFkName('downloadable_link_title', 'link_id', 'downloadable_link', 'link_id'),
        'link_id', $installer->getTable('downloadable_link'), 'link_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addIndex($installer->getIdxName('downloadable_link_title', 'store_id'), 'store_id')
    ->addForeignKey($installer->getFkName('downloadable_link_title', 'store_id', 'core_store', 'store_id'),
        'store_id', $installer->getTable('core_store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Link Title Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'downloadable_sample'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('downloadable_sample'))
    ->addColumn('sample_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Sample ID')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Product ID')
    ->addColumn('sample_url', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Sample URL')
    ->addColumn('sample_file', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Sample file')
    ->addColumn('sample_type', Varien_Db_Ddl_Table::TYPE_TEXT, 20, array(
        ), 'Sample Type')
    ->addColumn('sort_order', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Sort Order')
    ->addIndex($installer->getIdxName('downloadable_sample', 'product_id'), 'product_id')
    ->addForeignKey($installer->getFkName('downloadable_sample', 'product_id', 'catalog_product_entity', 'entity_id'),
        'product_id', $installer->getTable('catalog_product_entity'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Downloadable Sample Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'downloadable_sample_title'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('downloadable_sample_title'))
    ->addColumn('title_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Title ID')
    ->addColumn('sample_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Sample ID')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Store ID')
    ->addColumn('title', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Title')
    ->addIndex(
        $installer->getIdxName(
            'downloadable_sample_title',
            array('sample_id', 'store_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('sample_id', 'store_id'),
        array('type'=>Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName('downloadable_sample_title', 'sample_id'), 'sample_id')
    ->addForeignKey($installer->getFkName('downloadable_sample_title', 'sample_id', 'downloadable_sample', 'sample_id'),
        'sample_id', $installer->getTable('downloadable_sample'), 'sample_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addIndex($installer->getIdxName('downloadable_sample_title', 'store_id'), 'store_id')
    ->addForeignKey($installer->getFkName('downloadable_sample_title', 'store_id', 'core_store', 'store_id'),
        'store_id', $installer->getTable('core_store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Downloadable Sample Title Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_index_price_downlod_idx'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('catalog_product_index_price_downlod_idx'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Entity ID')
    ->addColumn('customer_group_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Customer Group ID')
    ->addColumn('website_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Website ID')
    ->addColumn('min_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Minimum price')
    ->addColumn('max_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Maximum price')
    ->setComment('Indexer Table for price of downloadable products');
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_index_price_downlod_tmp'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('catalog_product_index_price_downlod_tmp'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Entity ID')
    ->addColumn('customer_group_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Customer Group ID')
    ->addColumn('website_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Website ID')
    ->addColumn('min_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Minimum price')
    ->addColumn('max_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Maximum price')
    ->setComment('Temporary Indexer Table for price of downloadable products')
    ->setOption('type', 'MEMORY');
$installer->getConnection()->createTable($table);


/**
 * Add attributes to the eav/attribute table
 */
$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'links_purchased_separately', array(
    'type'                    => 'int',
    'backend'                 => '',
    'frontend'                => '',
    'label'                   => 'Links can be purchased separately',
    'input'                   => '',
    'class'                   => '',
    'source'                  => '',
    'global'                  => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'                 => false,
    'required'                => true,
    'user_defined'            => false,
    'default'                 => '',
    'searchable'              => false,
    'filterable'              => false,
    'comparable'              => false,
    'visible_on_front'        => false,
    'unique'                  => false,
    'apply_to'                => 'downloadable',
    'is_configurable'         => false,
    'used_in_product_listing' => true
));

$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'samples_title', array(
    'type'              => 'varchar',
    'backend'           => '',
    'frontend'          => '',
    'label'             => 'Samples title',
    'input'             => '',
    'class'             => '',
    'source'            => '',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible'           => false,
    'required'          => true,
    'user_defined'      => false,
    'default'           => '',
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'unique'            => false,
    'apply_to'          => 'downloadable',
    'is_configurable'   => false
));

$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'links_title', array(
    'type'              => 'varchar',
    'backend'           => '',
    'frontend'          => '',
    'label'             => 'Links title',
    'input'             => '',
    'class'             => '',
    'source'            => '',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible'           => false,
    'required'          => true,
    'user_defined'      => false,
    'default'           => '',
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'unique'            => false,
    'apply_to'          => 'downloadable',
    'is_configurable'   => false
));

$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'links_exist', array(
    'type'                      => 'int',
    'backend'                   => '',
    'frontend'                  => '',
    'label'                     => '',
    'input'                     => '',
    'class'                     => '',
    'source'                    => '',
    'global'                    => true,
    'visible'                   => false,
    'required'                  => false,
    'user_defined'              => false,
    'default'                   => '0',
    'searchable'                => false,
    'filterable'                => false,
    'comparable'                => false,
    'visible_on_front'          => false,
    'unique'                    => false,
    'apply_to'                  => 'downloadable',
    'is_configurable'           => false,
    'used_in_product_listing'   => 1
));

$installer->endSetup();
