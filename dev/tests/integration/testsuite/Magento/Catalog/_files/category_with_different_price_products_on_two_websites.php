<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/category_with_different_price_products.php';
require __DIR__ . '/../../Store/_files/second_website_with_two_stores.php';

$objectManager =  Bootstrap::getObjectManager();
/** @var WebsiteRepositoryInterface $websiteRepository */
$websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
$defaultWebsiteId = $websiteRepository->get('base')->getId();
$websiteId = $websiteRepository->get('test')->getId();

$product = $productRepository->get('simple1000');
$product->setWebsiteIds([$defaultWebsiteId, $websiteId]);
$productRepository->save($product);

$product = $productRepository->get('simple1001');
$product->setWebsiteIds([$defaultWebsiteId, $websiteId]);
$productRepository->save($product);
