<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/../../Catalog/_files/product_text_attribute.php';
require __DIR__ . '/../../Catalog/_files/product_simple.php';

$product->setData('text_attribute', 'test value');

/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryFactory $productRepository */
$productRepository = $objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
$productRepository->save($product);
