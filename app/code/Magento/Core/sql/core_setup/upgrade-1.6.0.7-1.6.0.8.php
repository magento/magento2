<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/* @var $installer \Magento\Setup\Module\SetupModule */
$installer = $this;

$installer->startSetup();
$connection = $installer->getConnection();

/**
 * Add column 'updated_at' to 'core_layout_update'
 */
$connection->addColumn(
    $installer->getTable('core_layout_update'),
    'updated_at',
    ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, 'nullable' => true, 'comment' => 'Last Update Timestamp']
);

$installer->endSetup();
