<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var WebsiteRepositoryInterface $websiteRepository */
$websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
$websiteCodes = ['eu_website', 'us_website', 'global_website'];
$websiteIds = [];
foreach ($websiteCodes as $websiteCode) {
    $website = $websiteRepository->get($websiteCode);
    $websiteIds[] = $website->getId();
}

$skus = ['SKU-1', 'SKU-2', 'SKU-3', 'SKU-4'];
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
foreach ($skus as $sku) {
    $product = $productRepository->get($sku);
    $product->setWebsiteIds($websiteIds);
    $productRepository->save($product);
}
