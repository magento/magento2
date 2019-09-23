<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ProductAlert\Model\ResourceModel\Stock;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
$resource = $objectManager->get(Stock::class);

$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$productId = $productRepository->get('simple-out-of-stock')->getId();

$resource->getConnection()->delete(
    $resource->getMainTable(),
    ['product_id = ?' => $productId]
);
