<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $installer \Magento\Catalog\Setup\CategorySetup */
$installer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Setup\CategorySetup');

$installer->updateAttribute('catalog_product', 'weight', 'is_filterable', 1);
