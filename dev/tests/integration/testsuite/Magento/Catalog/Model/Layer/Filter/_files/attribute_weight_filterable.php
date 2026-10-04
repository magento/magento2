<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

/** @var $installer \Magento\Catalog\Setup\CategorySetup */
$installer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Catalog\Setup\CategorySetup::class
);

$installer->updateAttribute('catalog_product', 'weight', 'is_filterable', 1);
