<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var ObjectManagerInterface $objectManager */
$objectManager = Bootstrap::getObjectManager();
/** @var ProductFactory $productFactory */
$productFactory = $objectManager->get(ProductFactory::class);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);

/** @var Product $product */
$product = $productFactory->create();
$product->setTypeId(Type::TYPE_SIMPLE)
    ->setAttributeSetId($product->getDefaultAttributeSetId())
    ->setName('New Product')
    ->setSku('simple')
    ->setPrice(10)
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setWebsiteIds([1])
    ->setStockData(['qty' => 100, 'is_in_stock' => 1, 'manage_stock' => 1])
    ->setNewsFromDate(date('Y-m-d H:i:s', strtotime('-2 day')))
    ->setNewsToDate(date('Y-m-d H:i:s', strtotime('+2 day')))
    ->setDescription('description')
    ->setShortDescription('short desc');

$productRepository->save($product);
