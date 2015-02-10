<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/* @var $installer \Magento\Framework\Setup\ModuleResourceInterface */
$installer = $this;

$installer->startSetup();
$connection = $installer->getConnection();

/**
 * Remove column 'theme_version' from 'core_theme'
 */
$connection->dropColumn(
    $installer->getTable('theme'),
    'theme_version'
);

$installer->endSetup();
