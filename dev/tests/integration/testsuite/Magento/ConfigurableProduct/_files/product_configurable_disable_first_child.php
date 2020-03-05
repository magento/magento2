<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

require __DIR__ . '/product_configurable_sku.php';

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Product\Action;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\TestFramework\Helper\Bootstrap;

$childSku = 'simple_10';

$childProduct = $productRepository->get($childSku);
$productAction = Bootstrap::getObjectManager()->get(Action::class);
$productAction->updateAttributes(
    [$childProduct->getEntityId()],
    [ProductAttributeInterface::CODE_STATUS => Status::STATUS_DISABLED],
    $childProduct->getStoreId()
);
