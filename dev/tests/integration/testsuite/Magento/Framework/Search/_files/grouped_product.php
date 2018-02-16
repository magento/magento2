<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require 'custom_product_tax_class.php';

use Magento\Catalog\Api\Data\ProductLinkInterfaceFactory;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Tax\Api\TaxClassManagementInterface;
use Magento\Tax\Api\TaxClassRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var $objectManager \Magento\Framework\ObjectManagerInterface */
$objectManager = Bootstrap::getObjectManager();

/** @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository */
$attributeRepository = $objectManager->create(ProductAttributeRepositoryInterface::class);
$attribute = $attributeRepository->get('tax_class_id');
$attribute->setIsFilterableInSearch(true);
$attribute->save();

/** @var SearchCriteriaBuilder $searchCriteriaBuilder */
$searchCriteriaBuilder = $objectManager->create(SearchCriteriaBuilder::class);
$searchCriteria = $searchCriteriaBuilder->addFilter(
    'class_type',
    TaxClassManagementInterface::TYPE_PRODUCT
)->create();

/** @var TaxClassRepositoryInterface $taxClassRepository */
$taxClassRepository = $objectManager->get(TaxClassRepositoryInterface::class);
$productTaxClasses = $taxClassRepository->getList($searchCriteria);
$productTaxClasses = $productTaxClasses->getItems();

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
$position = 1;

foreach ($productTaxClasses as $taxClass) {
    /** @var Product $product */
    $product = $objectManager->create(Product::class);
    $product->isObjectNew(true);
    $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
        ->setAttributeSetId(4)
        ->setWebsiteIds([1])
        ->setName('Grouped association ' . $position)
        ->setSku('grouped-association-' . $position)
        ->setPrice(10)
        ->setTaxClassId($taxClass->getClassId())
        ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
        ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
        ->setStockData(['is_in_stock' => 1, 'qty' => 10]);

    $position++;

    $productRepository->save($product);
}

/** @var Product $product */
$product = $objectManager->create(Product::class);
$product->isObjectNew(true);
$product->setTypeId(\Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Grouped Product')
    ->setSku('grouped-product')
    ->setPrice(100)
    ->setTaxClassId(0)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['is_in_stock' => 1]);

$newLinks = [];
$productLinkFactory = $objectManager->get(ProductLinkInterfaceFactory::class);

$associatedProducts = ['grouped-association-1', 'grouped-association-2'];
$position = 1;

foreach ($associatedProducts as $sku) {
    $linkedProduct = $productRepository->get($sku, false, null, true);
    
    /** @var \Magento\Catalog\Api\Data\ProductLinkInterface $productLink */
    $productLink = $productLinkFactory->create();

    $productLink->setSku($product->getSku())
        ->setLinkType('associated')
        ->setLinkedProductSku($linkedProduct->getSku())
        ->setLinkedProductType($linkedProduct->getTypeId())
        ->setPosition($position)
        ->getExtensionAttributes()
        ->setQty(1);

    $position++;
    $newLinks[] = $productLink;
}

$product->setProductLinks($newLinks);
$productRepository->save($product);
