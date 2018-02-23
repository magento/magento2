<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

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

$skus = ['SKU-1', 'SKU-2', 'SKU-3'];
$productRepository = $objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
foreach ($skus as $sku) {
    /** @var \Magento\Catalog\Model\Product $product */
    $product = $productRepository->get($sku);
    $product->setWebsiteIds($websiteIds);
    $productRepository->save($product);
}
