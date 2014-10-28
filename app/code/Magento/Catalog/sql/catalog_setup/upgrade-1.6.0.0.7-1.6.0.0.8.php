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

/** @var $installer \Magento\Catalog\Model\Resource\Setup */
$installer = $this;
$connection = $installer->getConnection();

$connection->addIndex(
    $installer->getTable('catalog_category_product_index_tmp'),
    $installer->getIdxName('catalog_category_product_index_tmp', array('product_id', 'category_id', 'store_id')),
    array('product_id', 'category_id', 'store_id')
);

$table = $installer->getTable('catalog_category_product_index_enbl_idx');
$connection->dropIndex($table, 'IDX_CATALOG_CATEGORY_PRODUCT_INDEX_ENBL_IDX_PRODUCT_ID');
$connection->addIndex(
    $table,
    $installer->getIdxName('catalog_category_product_index_enbl_idx', array('product_id', 'visibility')),
    array('product_id', 'visibility')
);


$table = $installer->getTable('catalog_category_product_index_enbl_tmp');
$connection->dropIndex($table, 'IDX_CATALOG_CATEGORY_PRODUCT_INDEX_ENBL_TMP_PRODUCT_ID');
$connection->addIndex(
    $table,
    $installer->getIdxName('catalog_category_product_index_enbl_tmp', array('product_id', 'visibility')),
    array('product_id', 'visibility')
);

$connection->addIndex(
    $installer->getTable('catalog_category_anc_products_index_idx'),
    $installer->getIdxName('catalog_category_anc_products_index_idx', array('category_id', 'product_id', 'position')),
    array('category_id', 'product_id', 'position')
);

$connection->addIndex(
    $installer->getTable('catalog_category_anc_products_index_tmp'),
    $installer->getIdxName('catalog_category_anc_products_index_tmp', array('category_id', 'product_id', 'position')),
    array('category_id', 'product_id', 'position')
);

$connection->addIndex(
    $installer->getTable('catalog_category_anc_categs_index_idx'),
    $installer->getIdxName('catalog_category_anc_categs_index_idx', array('path', 'category_id')),
    array('path', 'category_id')
);

$connection->addIndex(
    $installer->getTable('catalog_category_anc_categs_index_tmp'),
    $installer->getIdxName('catalog_category_anc_categs_index_tmp', array('path', 'category_id')),
    array('path', 'category_id')
);
