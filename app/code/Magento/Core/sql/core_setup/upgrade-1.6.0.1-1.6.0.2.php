<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/* @var $installer \Magento\Setup\Module\SetupModule */
$installer = $this;

$installer->startSetup();

$installer->getConnection()->dropForeignKey(
    $installer->getTable('core_cache_tag'),
    $installer->getFkName('core_cache_tag', 'cache_id', 'core_cache', 'id')
);

$installer->endSetup();
