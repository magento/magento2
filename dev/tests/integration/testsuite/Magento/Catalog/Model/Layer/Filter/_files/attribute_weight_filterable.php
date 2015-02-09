<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $installer \Magento\Catalog\Model\Resource\Setup */
$installer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Catalog\Model\Resource\Setup',
    ['resourceName' => 'catalog_setup']
);

$installer->updateAttribute('catalog_product', 'weight', 'is_filterable', 1);
