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

/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);

$skuList = ['simple_11', 'simple_22', 'grouped'];
foreach ($skuList as $sku) {
    try {
        $product = $productRepository->get($sku, false, null, true);

        $stockStatus = $objectManager->create(\Magento\CatalogInventory\Model\Stock\Status::class);
        $stockStatus->load($product->getEntityId(), 'product_id');
        $stockStatus->delete();

        $productRepository->delete($product);
    } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
        //Product already removed
    }
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
