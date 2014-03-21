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
 * @package     Magento_Cms
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$installer = $this;
/** @var $installer \Magento\Catalog\Model\Resource\Setup */
$table = $installer->getConnection()->newTable(
    $installer->getTable('cms_url_rewrite')
)->addColumn(
    'cms_rewrite_id',
    \Magento\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'nullable' => false, 'primary' => true),
    'Cms Url Rewrite ID'
)->addColumn(
    'url_rewrite_id',
    \Magento\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false),
    'Core Url Rewrite ID'
)->addIndex(
    $installer->getIdxName('cms_url_rewrite', array('url_rewrite_id')),
    array('url_rewrite_id')
)->addForeignKey(
    $installer->getFkName('cms_url_rewrite', 'url_rewrite_id', 'core_url_rewrite', 'url_rewrite_id'),
    'url_rewrite_id',
    $installer->getTable('core_url_rewrite'),
    'url_rewrite_id',
    \Magento\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\DB\Ddl\Table::ACTION_CASCADE
)->addColumn(
    'cms_page_id',
    \Magento\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('nullable' => false),
    'Cms Page ID'
)->addIndex(
    $installer->getIdxName('cms_url_rewrite', array('cms_page_id')),
    array('cms_page_id')
)->addForeignKey(
    $installer->getFkName('cms_url_rewrite', 'cms_page_id', 'cms_page', 'page_id'),
    'cms_page_id',
    $installer->getTable('cms_page'),
    'page_id',
    \Magento\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Cms Url Rewrite Table'
);
$installer->getConnection()->createTable($table);
