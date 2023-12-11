<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\TestFramework\Helper\Bootstrap;

$product = Bootstrap::getObjectManager()->create(ProductFactory::class)->create();
$productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
$product->setTypeId('simple')
    ->setPageLayout('3columns')
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Simple Product With Custom Design')
    ->setSku('simple_with_custom_design')
    ->setPrice(10)
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_in_stock' => 1]);

$productRepository->save($product);
