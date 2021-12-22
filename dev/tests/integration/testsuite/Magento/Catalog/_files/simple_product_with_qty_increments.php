<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var ProductInterfaceFactory ProductInterfaceFactory */
$productFactory = $objectManager->get(ProductInterfaceFactory::class);
/** @var ProductRepositoryInterface $productRepositoryFactory */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$productRepository->cleanCache();

$product = $productFactory->create();
$product->setTypeId(Type::TYPE_SIMPLE)
    ->setStatus(Status::STATUS_ENABLED)
    ->setAttributeSetId($product->getDefaultAttributeSetId())
    ->setWebsiteIds([1])
    ->setName('Simple Product with qty increments')
    ->setSku('simple_product_with_qty_increments')
    ->setPrice(10)
    ->setWeight(1)
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStockData(
        [
            'use_config_manage_stock' => 1,
            'qty' => 100,
            'is_qty_decimal' => 0,
            'is_in_stock' => 1,
            'enable_qty_increments' => 1,
            'qty_increments' => 3,
        ]
    )
    ->setCanSaveCustomOptions(true)
    ->setHasOptions(true);
$productRepository->save($product);
