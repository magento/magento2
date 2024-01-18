<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);

$product = $productRepository->get('simple_product');
$product->setSpecialPrice('5.99');

$product->setSpecialFromDate(date('Y-m-d', strtotime('+3 day')));
$product->setSpecialToDate(date('Y-m-d', strtotime('+5 day')));

$productRepository->save($product);
