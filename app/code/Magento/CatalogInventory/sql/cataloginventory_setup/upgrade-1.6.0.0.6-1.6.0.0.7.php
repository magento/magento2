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

/** @var $this \Magento\Eav\Model\Entity\Setup */

$this->startSetup();
/**
 * Add new field to 'cataloginventory_stock_item'
 */
$this->getConnection()->addColumn(
    $this->getTable('cataloginventory_stock'),
    'website_id',
    array(
        'TYPE' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        'LENGTH' => 5,
        'UNSIGNED' => true,
        'NULLABLE' => false,
        'COMMENT' => 'Website Id'
    )
);
$this->getConnection()->addIndex(
    $this->getTable('cataloginventory_stock'),
    $this->getIdxName(
        'cataloginventory_stock',
        array('website_id'),
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    array('website_id'),
    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
);

$this->getConnection()->dropIndex(
    $this->getTable('cataloginventory_stock_item'),
    $this->getIdxName(
        'cataloginventory_stock_item',
        array('product_id', 'stock_id'),
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    )
);

$this->getConnection()->addIndex(
    $this->getTable('cataloginventory_stock_item'),
    $this->getIdxName(
        'cataloginventory_stock_item',
        array('product_id', 'website_id'),
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    array('product_id', 'website_id'),
    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
);
$this->endSetup();
