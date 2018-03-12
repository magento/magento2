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

/** @var \Magento\Store\Model\Website $website */
$website = $objectManager->create(\Magento\Store\Model\Website::class);
$website->load('eu_website');

$stock = $objectManager->create(\Magento\ProductAlert\Model\Stock::class);
$stock->setCustomerId(2)
    ->setProductId($product->getId())
    ->setWebsiteId($website->getId())
    ->save();
