<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\TestFramework\Helper\Bootstrap;

\Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize();

/** @var ProductRepositoryInterface $productRepository */
$productRepository = Bootstrap::getObjectManager()
    ->get(ProductRepositoryInterface::class);

$productLinkFactory = Bootstrap::getObjectManager()
    ->get(\Magento\Catalog\Api\Data\ProductLinkInterfaceFactory::class);
$productIds = ['11', '22'];

foreach ($productIds as $productId) {
    /** @var $product Product */
    $product = Bootstrap::getObjectManager()->create(Product::class);
    $product->setTypeId(Type::TYPE_SIMPLE)
        ->setId($productId)
        ->setWebsiteIds([1])
        ->setAttributeSetId(4)
        ->setName('Simple ' . $productId)
        ->setSku('simple_' . $productId)
        ->setPrice(100)
        ->setVisibility(Visibility::VISIBILITY_BOTH)
        ->setStatus(Status::STATUS_ENABLED)
        ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1]);

    $linkedProducts[] = $productRepository->save($product);
}

/** @var $groupedProductInStock Product */
$groupedProductInStock = Bootstrap::getObjectManager()->create(Product::class);

$groupedProductInStock->setTypeId(Grouped::TYPE_CODE)
    ->setId(1)
    ->setWebsiteIds([1])
    ->setAttributeSetId(4)
    ->setName('Grouped Product In Stock')
    ->setSku('grouped_in_stock')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'is_in_stock' => 1]);

foreach ($linkedProducts as $linkedProduct) {
    /** @var \Magento\Catalog\Api\Data\ProductLinkInterface $productLink */
    $productLink = $productLinkFactory->create();
    $productLink->setSku($groupedProductInStock->getSku())
        ->setLinkType('associated')
        ->setLinkedProductSku($linkedProduct->getSku())
        ->setLinkedProductType($linkedProduct->getTypeId())
        ->getExtensionAttributes()
        ->setQty(1);
    $newLinks[] = $productLink;
}

$groupedProductInStock->setProductLinks($newLinks);

$productRepository->save($groupedProductInStock);

/** @var $groupedProductOutOfStock Product */
$groupedProductOutOfStock = Bootstrap::getObjectManager()->create(Product::class);

$groupedProductOutOfStock->setTypeId(Grouped::TYPE_CODE)
    ->setId(12)
    ->setWebsiteIds([1])
    ->setAttributeSetId(4)
    ->setName('Grouped Product Out Of Stock')
    ->setSku('grouped_out_of_stock')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'is_in_stock' => 0]);

foreach ($linkedProducts as $linkedProduct) {
    /** @var \Magento\Catalog\Api\Data\ProductLinkInterface $productLink */
    $productLink = $productLinkFactory->create();
    $productLink->setSku($groupedProductOutOfStock->getSku())
        ->setLinkType('associated')
        ->setLinkedProductSku($linkedProduct->getSku())
        ->setLinkedProductType($linkedProduct->getTypeId())
        ->getExtensionAttributes()
        ->setQty(1);
    $newLinks[] = $productLink;
}

$groupedProductOutOfStock->setProductLinks($newLinks);

$productRepository->save($groupedProductOutOfStock);
