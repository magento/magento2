<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Framework\Registry $registry */
$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$collection = $objectManager->create(\Magento\Catalog\Model\ResourceModel\Product\Collection::class);

foreach ($collection as $product) {
    /** @var \Magento\Catalog\Model\Product $category */
    $product->delete();
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
