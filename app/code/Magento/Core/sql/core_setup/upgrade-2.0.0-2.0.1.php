<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/* @var $installer \Magento\Setup\Module\SetupModule */
$installer = $this;

$installer->startSetup();
$connection = $installer->getConnection();

/**
 * Remove column 'theme_version' from 'core_theme'
 */
$connection->dropColumn(
    $installer->getTable('core_theme'),
    'theme_version'
);

$installer->endSetup();
