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
$productFactory = $objectManager->get(ProductInterfaceFactory::class);
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$productRepository->cleanCache();

for ($i = 1; $i <= 4; $i++) {
    $product = $productFactory->create();
    $product->setTypeId(Type::TYPE_SIMPLE)
        ->setAttributeSetId(4)
        ->setName('Simple Product ' . $i)
        ->setSku('sku' . $i)
        ->setPrice(10)
        ->setStockData(['qty' => 100, 'is_in_stock' => true, 'manage_stock' => true])
        ->setStatus(Status::STATUS_ENABLED);
    $productRepository->save($product);
}
