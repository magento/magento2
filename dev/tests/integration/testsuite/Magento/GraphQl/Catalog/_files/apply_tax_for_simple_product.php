<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Tax\Model\ClassModel as TaxClassModel;
use Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory as TaxClassCollectionFactory;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$product = $productRepository->get('simple_product');

/** @var TaxClassCollectionFactory $taxClassCollectionFactory */
$taxClassCollectionFactory = $objectManager->get(TaxClassCollectionFactory::class);
$taxClassCollection = $taxClassCollectionFactory->create();

/** @var TaxClassModel $taxClass */
$taxClassCollection->addFieldToFilter('class_type', TaxClassModel::TAX_CLASS_TYPE_PRODUCT);
$taxClass = $taxClassCollection->getFirstItem();

$product->setCustomAttribute('tax_class_id', $taxClass->getClassId());
$productRepository->save($product);
