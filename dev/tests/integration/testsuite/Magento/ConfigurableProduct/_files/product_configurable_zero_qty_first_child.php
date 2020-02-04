<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

require __DIR__ . '/product_configurable_sku.php';

$childSku = 'simple_10';

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
