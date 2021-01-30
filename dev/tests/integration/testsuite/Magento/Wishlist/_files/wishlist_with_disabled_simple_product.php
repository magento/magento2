<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
