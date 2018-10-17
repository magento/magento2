<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Framework\Registry $registry */
$registry = $objectManager->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

//Remove categories
/** @var Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
$collection = $objectManager->create(\Magento\Catalog\Model\ResourceModel\Category\Collection::class);
$collection->addAttributeToFilter('level', ['gt' => 1]);

foreach ($collection as $category) {
    /** @var \Magento\Catalog\Model\Category $category */
    if ($category->getLevel() !== 1) {
        $category->delete();
    }
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
