<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\TestFramework\Helper\Bootstrap;

/** @var ProductRepositoryInterface $productRepository */
$productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);

try {
    $configurableProduct = $productRepository->get('configurable');
    $productTypeInstance = $configurableProduct->getTypeInstance();

    /** @var ProductModel $child */
    foreach ($productTypeInstance->getUsedProducts($configurableProduct) as $child) {
        $childProduct = $productRepository->getById($child->getId());
        $childProduct->setStockData(['use_config_manage_stock' => 1, 'qty' => 0, 'is_qty_decimal' => 0, 'is_in_stock' => 0]);
        $productRepository->save($childProduct);
        break;
    }
} catch (Exception $e) {
    // Nothing to remove
}
