<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Store\Model\Store;

require __DIR__ . '/category_with_different_price_products.php';

$product = $productFactory->create();
$product->setTypeId(Type::TYPE_SIMPLE)
    ->setAttributeSetId(4)
    ->setStoreId(Store::DEFAULT_STORE_ID)
    ->setWebsiteIds([1])
    ->setName('Simple Product2')
    ->setSku('simple1002')
    ->setPrice(10)
    ->setWeight(1)
    ->setStockData(['use_config_manage_stock' => 0])
    ->setCategoryIds([$category->getId()])
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED);
$productRepository->save($product);
