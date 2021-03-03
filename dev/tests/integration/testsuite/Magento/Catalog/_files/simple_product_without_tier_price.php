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
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var WebsiteInterface $adminWebsite */
$adminWebsite = $objectManager->get(WebsiteRepositoryInterface::class)->get('admin');

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
/** @var $product Product */
$product = $objectManager->create(Product::class);
$product->setTypeId(Type::TYPE_SIMPLE)
->setAttributeSetId($product->getDefaultAttributeSetId())
->setWebsiteIds([1])
->setName('Simple Product 19')
->setSku('simple_19')
->setPrice(22)
->setWeight(1)
->setVisibility(Visibility::VISIBILITY_BOTH)
->setStatus(Status::STATUS_ENABLED)
->setStockData(
    [
        'use_config_manage_stock' => 1,
        'qty' => 100,
        'is_qty_decimal' => 0,
        'is_in_stock' => 1,
    ]
);

$productRepository->save($product);
