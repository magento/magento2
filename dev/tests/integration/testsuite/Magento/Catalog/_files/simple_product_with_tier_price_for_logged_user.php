<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

require __DIR__ . '/product_simple.php';

$product = $productRepository->get('simple', false, null, true);
$tierPrices = $product->getTierPrices() ?? [];
$tierPriceExtensionAttributes = $tpExtensionAttributesFactory->create()->setWebsiteId($adminWebsite->getId());
$tierPrices[] = $tierPriceFactory->create(
    [
        'data' => [
            'customer_group_id' => 1,
            'qty' => 3,
            'value' => 1
        ]
    ]
)->setExtensionAttributes($tierPriceExtensionAttributes);
$product->setTierPrices($tierPrices);
$productRepository->save($product);
