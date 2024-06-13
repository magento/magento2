<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */

use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Wishlist/_files/wishlist.php');

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$productRepository = $objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);

$productSku = 'simple';
$product = $productRepository->get($productSku);
$product->setStatus(ProductStatus::STATUS_DISABLED);
$productRepository->save($product);
