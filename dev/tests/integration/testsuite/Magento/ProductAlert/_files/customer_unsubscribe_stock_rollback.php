<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
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
