<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/* @var $installer \Magento\Setup\Module\SetupModule */
$installer = $this;

$installer->startSetup();

$connection = $installer->getConnection();
$table = $installer->getTable('core_theme');

$connection->dropColumn($table, 'magento_version_from');
$connection->dropColumn($table, 'magento_version_to');

$installer->endSetup();
