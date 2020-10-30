<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Downloadable\Model\Product\Type as ProductType;
use Magento\Catalog\Model\Product\Visibility as ProductVisibility;
use Magento\Downloadable\Api\DomainManagerInterface;

/** @var DomainManagerInterface $domainManager */
$domainManager = Bootstrap::getObjectManager()->get(DomainManagerInterface::class);
$domainManager->addDomains(['example.com']);

/**
 * @var \Magento\Catalog\Model\Product $product
 */
$product = Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
$product
    ->setTypeId(ProductType::TYPE_DOWNLOADABLE)
    ->setAttributeSetId(4)
    ->setStoreId(1)
    ->setWebsiteIds([1])
    ->setName('Downloadable Product (Links can not be purchased separately)')
    ->setSku('downloadable-product-without-purchased-separately-links')
    ->setPrice(10)
    ->setVisibility(ProductVisibility::VISIBILITY_BOTH)
    ->setStatus(ProductStatus::STATUS_ENABLED)
    ->setStockData(
        [
            'qty' => 100,
            'is_in_stock' => 1,
            'manage_stock' => 1,
            ]
    );

/**
 * @var \Magento\Downloadable\Api\Data\LinkInterfaceFactory $linkFactory1
 */
$linkFactory1 = Bootstrap::getObjectManager()
    ->get(\Magento\Downloadable\Api\Data\LinkInterfaceFactory::class);
$link1 = $linkFactory1->create();
$link1
    ->setTitle('Downloadable Product Link 1')
    ->setLinkType(\Magento\Downloadable\Helper\Download::LINK_TYPE_URL)
    ->setIsShareable(\Magento\Downloadable\Model\Link::LINK_SHAREABLE_CONFIG)
    ->setLinkUrl('http://example.com/downloadable1.txt')
    ->setStoreId($product->getStoreId())
    ->setWebsiteId($product->getStore()->getWebsiteId())
    ->setProductWebsiteIds($product->getWebsiteIds())
    ->setSortOrder(10)
    ->setPrice(2.0000)
    ->setNumberOfDownloads(0);
/**
 * @var \Magento\Downloadable\Api\Data\LinkInterfaceFactory $linkFactory2
 */
$linkFactory2 = Bootstrap::getObjectManager()
    ->get(\Magento\Downloadable\Api\Data\LinkInterfaceFactory::class);
$link2 = $linkFactory2->create();
$link2
    ->setTitle('Downloadable Product Link 2')
    ->setLinkType(\Magento\Downloadable\Helper\Download::LINK_TYPE_URL)
    ->setIsShareable(\Magento\Downloadable\Model\Link::LINK_SHAREABLE_CONFIG)
    ->setLinkUrl('http://example.com/downloadable2.txt')
    ->setStoreId($product->getStoreId())
    ->setWebsiteId($product->getStore()->getWebsiteId())
    ->setProductWebsiteIds($product->getWebsiteIds())
    ->setSortOrder(20)
    ->setPrice(4.0000)
    ->setNumberOfDownloads(0);

$extension = $product->getExtensionAttributes();
$extension->setDownloadableProductLinks([$link1, $link2]);

$product->setExtensionAttributes($extension);
$product->setLinksPurchasedSeparately(false);

$productRepository = Bootstrap::getObjectManager()
    ->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
$productRepository->save($product);
