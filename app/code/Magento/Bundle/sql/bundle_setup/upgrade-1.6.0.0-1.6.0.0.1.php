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
/** @var $connection \Magento\Framework\DB\Adapter\Pdo\Mysql */
$connection = $installer->getConnection();

$priceIndexerTables = array('catalog_product_index_price_bundle_idx', 'catalog_product_index_price_bundle_tmp');

$optionsPriceIndexerTables = array(
    'catalog_product_index_price_bundle_opt_idx',
    'catalog_product_index_price_bundle_opt_tmp'
);

$selectionPriceIndexerTables = array(
    'catalog_product_index_price_bundle_sel_idx',
    'catalog_product_index_price_bundle_sel_tmp'
);

foreach ($priceIndexerTables as $table) {
    $connection->addColumn(
        $installer->getTable($table),
        'group_price',
        array('type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL, 'length' => '12,4', 'comment' => 'Group price')
    );
    $connection->addColumn(
        $installer->getTable($table),
        'base_group_price',
        array('type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL, 'length' => '12,4', 'comment' => 'Base Group Price')
    );
    $connection->addColumn(
        $installer->getTable($table),
        'group_price_percent',
        array('type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL, 'length' => '12,4', 'comment' => 'Group Price Percent')
    );
}

foreach (array_merge($optionsPriceIndexerTables, $selectionPriceIndexerTables) as $table) {
    $connection->addColumn(
        $installer->getTable($table),
        'group_price',
        array('type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL, 'length' => '12,4', 'comment' => 'Group price')
    );
}

foreach ($optionsPriceIndexerTables as $table) {
    $connection->addColumn(
        $installer->getTable($table),
        'alt_group_price',
        array('type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL, 'length' => '12,4', 'comment' => 'Alt Group Price')
    );
}

$memoryTables = array(
    'catalog_product_index_price_bundle_opt_tmp',
    'catalog_product_index_price_bundle_sel_tmp',
    'catalog_product_index_price_bundle_tmp'
);

foreach ($memoryTables as $table) {
    $connection->changeTableEngine($this->getTable($table), \Magento\Framework\DB\Adapter\Pdo\Mysql::ENGINE_MEMORY);
}
