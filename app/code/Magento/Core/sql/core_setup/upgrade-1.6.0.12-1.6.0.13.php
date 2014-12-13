<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/* @var $installer \Magento\Setup\Module\SetupModule */
$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn(
    $installer->getTable('core_theme'),
    'code',
    ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'comment' => 'Full theme code, including package']
);

$installer->endSetup();
