<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Framework\Registry $registry */
$registry = $objectManager->get(\Magento\Framework\Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$productRepository = $objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
foreach (['simple_10', 'simple_11', 'simple_12', 'configurable'] as $sku) {
    try {
        $product = $productRepository->get($sku, true);

        $stockStatus = $objectManager->create(\Magento\CatalogInventory\Model\Stock\Status::class);
        $stockStatus->load($product->getEntityId(), 'product_id');
        $stockStatus->delete();

        if ($product->getId()) {
            $productRepository->delete($product);
        }
    } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
        //Product already removed
    }
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);

require __DIR__ . '/configurable_attribute_with_source_model_rollback.php';
