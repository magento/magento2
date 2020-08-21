<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/ConfigurableProduct/_files/product_configurable_sku.php');

$objectManager = Bootstrap::getObjectManager();
$childSku = 'simple_10';
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
$childProduct = $productRepository->get($childSku);
$childProduct->setStockData(
    [
        'use_config_manage_stock' => 1,
        'qty' => 0,
        'is_qty_decimal' => 0,
        'is_in_stock' => 0
    ]
);
$productRepository->save($childProduct);
