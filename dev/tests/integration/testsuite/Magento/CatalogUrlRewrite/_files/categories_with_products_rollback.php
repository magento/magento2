<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\Registry');
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var $productCollection \Magento\Catalog\Model\ResourceModel\Product\Collection */
$productCollection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create('\Magento\Catalog\Model\ResourceModel\Product\Collection');

$productCollection->load()->delete();

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);


require __DIR__ . '/categories_rollback.php';
