<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\Data\ProductExtensionInterfaceFactory;
use Magento\Catalog\Api\Data\ProductTierPriceExtensionFactory;

/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
// simple product without custom options
$secondProductSku = 'simple-2';

/** @var \Magento\TestFramework\ObjectManager $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$productExtensionAttributesFactory = $objectManager->get(ProductExtensionInterfaceFactory::class);

$adminWebsite = $objectManager->get(\Magento\Store\Api\WebsiteRepositoryInterface::class)->get('admin');
/** @var  $tpExtensionAttributes */
$tpExtensionAttributesFactory = $objectManager->get(ProductTierPriceExtensionFactory::class);
$tierPriceExtensionAttributes1 = $tpExtensionAttributesFactory->create()
    ->setWebsiteId($adminWebsite->getId());
$productExtensionAttributesWebsiteIds = $productExtensionAttributesFactory->create(
    ['website_ids' => $adminWebsite->getId()]
);
/** @var \Magento\Catalog\Api\CategoryLinkManagementInterface $categoryLinkManagement */
$categoryLinkManagement = $objectManager->get(\Magento\Catalog\Api\CategoryLinkManagementInterface::class);
/** @var $secondProduct \Magento\Catalog\Model\Product */
$secondProduct = $objectManager->create(\Magento\Catalog\Model\Product::class);
$secondProduct->isObjectNew(true);
$secondProduct->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setId(22)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Simple Product 2')
    ->setSku($secondProductSku)
    ->setPrice(11)
    ->setWeight(1)
    ->setShortDescription("Short description 2")
    ->setTaxClassId(0)
    ->setDescription('Description with <b>html tag</b>')
    ->setExtensionAttributes($productExtensionAttributesWebsiteIds)
    ->setMetaTitle('meta title 2')
    ->setMetaKeyword('meta keyword 2')
    ->setMetaDescription('meta description 2')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(
        [
            'use_config_manage_stock'   => 1,
            'qty'                       => 100,
            'is_qty_decimal'            => 0,
            'is_in_stock'               => 1,
        ]
    )->setCanSaveCustomOptions(false)
    ->setHasOptions(false);

/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
$productRepository->save($secondProduct);

$categoryLinkManagement->assignProductToCategories(
    $secondProduct->getSku(),
    [2]
);
