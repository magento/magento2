<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Eav\Model\Config;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\Data\ProductLinkInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var WebsiteRepositoryInterface $websiteRepository */
$websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
$baseWebsite = $websiteRepository->get('base');
/** @var StoreManagerInterface $storeManager */
$storeManager = $objectManager->get(StoreManagerInterface::class);
$defaultAttributeSet = $objectManager->get(Config::class)->getEntityType(Product::ENTITY)->getDefaultAttributeSetId();
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
/** @var ProductInterfaceFactory $productInterfaceFactory */
$productInterfaceFactory = $objectManager->get(ProductInterfaceFactory::class);

/** @var Product $product */
$product = $productInterfaceFactory->create();
$product->setTypeId(Type::TYPE_SIMPLE)
    ->setAttributeSetId($defaultAttributeSet)
    ->setStoreId($storeManager->getDefaultStoreView()->getId())
    ->setWebsiteIds([$baseWebsite->getId()])
    ->setName('Simple Product')
    ->setSku('simple')
    ->setPrice(10)
    ->setWeight(18)
    ->setStockData(['use_config_manage_stock' => 0])
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED);

$simple = $productRepository->save($product);
$simple->setStoreId($storeManager->getDefaultStoreView()->getId())
->setStatus(Status::STATUS_DISABLED);
$productRepository->save($simple);
/** @var ProductLinkInterface $productLink */
$productLink = $objectManager->create(ProductLinkInterface::class);
$productLink->setSku('simple_with_related');
$productLink->setLinkedProductSku('simple');
$productLink->setPosition(1);
$productLink->setLinkType('related');

/** @var Product $product */
$product = $productInterfaceFactory->create();
$product->setTypeId(Type::TYPE_SIMPLE)
    ->setAttributeSetId($defaultAttributeSet)
    ->setStoreId($storeManager->getDefaultStoreView()->getId())
    ->setWebsiteIds([$baseWebsite->getId()])
    ->setName('Simple Product With Related Product')
    ->setSku('simple_with_related')
    ->setPrice(10)
    ->setWeight(18)
    ->setProductLinks([$productLink])
    ->setStockData(['use_config_manage_stock' => 0])
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED);

$productRepository->save($product);
