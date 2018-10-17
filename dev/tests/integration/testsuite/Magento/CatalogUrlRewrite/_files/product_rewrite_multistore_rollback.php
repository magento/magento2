<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

require __DIR__ . '/../../Store/_files/store_rollback.php';
require __DIR__ . '/../../Store/_files/second_store_rollback.php';
