<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory as TaxClassCollectionFactory;
use Magento\Tax\Model\ClassModel as TaxClassModel;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
/** @var ProductFactory $productFactory */
$productFactory = $objectManager->create(ProductFactory::class);
$product = $productFactory->create();
$product
    ->setTypeId('simple')
    ->setId(1)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Simple Product')
    ->setSku('simple')
    ->setPrice(10)
    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 22, 'is_in_stock' => 1])
    ->setQty(22);


/** @var TaxClassCollectionFactory $taxClassCollectionFactory */
$taxClassCollectionFactory = $objectManager->create(TaxClassCollectionFactory::class);
$taxClassCollection = $taxClassCollectionFactory->create();

/** @var TaxClassModel $taxClass */
$taxClassCollection->addFieldToFilter('class_type', TaxClassModel::TAX_CLASS_TYPE_PRODUCT);
$taxClass = $taxClassCollection->getFirstItem();

$product->setCustomAttribute('tax_class_id', $taxClass->getClassId());
$productRepository->save($product);
