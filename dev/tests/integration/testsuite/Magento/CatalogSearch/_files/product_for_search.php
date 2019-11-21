<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

require 'searchable_attribute.php';

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Action;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Eav\Model\Entity\Type as EntityType;
use Magento\Store\Model\StoreManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;

/** @var ObjectManager $objectManager */
$objectManager = Bootstrap::getObjectManager();
/** @var EntityType $entityType */
$entityType = $objectManager->create(EntityType::class);
$entityType = $entityType->loadByCode(ProductAttributeInterface::ENTITY_TYPE_CODE);
/** @var Action $productAction */
$productAction = $objectManager->create(Action::class);
/** @var StoreManager $storeManager */
$storeManager = $objectManager->get(StoreManager::class);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
/** @var Product $product */
$product = $objectManager->create(Product::class);
$product->isObjectNew(true);
$product->setTypeId(Type::TYPE_SIMPLE)
    ->setAttributeSetId($entityType->getEntityTypeId())
    ->setWebsiteIds([1])
    ->setName('Simple product name')
    ->setSku('simple_for_search')
    ->setPrice(100)
    ->setWeight(1)
    ->setShortDescription('Product short description')
    ->setTaxClassId(0)
    ->setDescription('Product description')
    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')
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
$storeManager->setIsSingleStoreModeAllowed(false);
$store = $storeManager->getStore('default');
$product = $productRepository->get('simple_for_search');
$productAction->updateAttributes(
    [$product->getId()],
    ['test_searchable_attribute' => $attribute->getSource()->getOptionId('Option 1')],
    $store->getId()
);
