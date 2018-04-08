<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$product = $productRepository->get('SKU-3');

$stock = $objectManager->create(\Magento\ProductAlert\Model\Stock::class);
$stock->setCustomerId(1)
    ->setProductId($product->getId())
    ->setWebsiteId(1)
    ->save();
