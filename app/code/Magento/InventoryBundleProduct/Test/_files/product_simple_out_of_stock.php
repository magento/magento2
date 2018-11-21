<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var ProductInterfaceFactory $productFactory */
$productFactory = $objectManager->get(ProductInterfaceFactory::class);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$productRepository->cleanCache();

$product = $productFactory->create();
$product->setTypeId(Type::TYPE_SIMPLE)
    ->setId(2)
    ->setAttributeSetId(4)
    ->setName('Simple Product Out Of Stock')
    ->setSku('simple-out-of-stock')
    ->setPrice(10)
    ->setStockData([
        'qty' => 0,
        'is_in_stock' => 1,
        'use_config_manage_stock' => 1,
        'use_config_backorders' => 1,
        'use_config_min_sale_qty' => 0,
        'min_sale_qty' => 1
    ])
    ->setStatus(Status::STATUS_ENABLED);
$productRepository->save($product);
