<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\TestFramework\Helper\Bootstrap as Bootstrap;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Downloadable\Model\Product\Type as ProductType;
use Magento\Catalog\Model\Product\Visibility as ProductVisibility;

/**
 * @var \Magento\Catalog\Model\Product $product
 */
$product = Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
$product->setTypeId(
    ProductType::TYPE_DOWNLOADABLE
)->setAttributeSetId(
    14
)->setWebsiteIds(
    [1]
)->setName(
    'GraphQl Downloadable Product'
)->setSku(
    'graphql-downloadable-product'
)->setPrice(
    10
)->setVisibility(
    ProductVisibility::VISIBILITY_BOTH
)->setStatus(
    ProductStatus::STATUS_ENABLED
)->setLinksPurchasedSeparately(
    false
)->setStockData(
    [
        'qty' => 100,
        'is_in_stock' => 1,
        'manage_stock' => 1,
    ]
);

/**
 * @var \Magento\Downloadable\Api\Data\LinkInterfaceFactory $linkFactory
 */
$linkFactory = Bootstrap::getObjectManager()
    ->get(\Magento\Downloadable\Api\Data\LinkInterfaceFactory::class);
$links = [];
$linkData = [
    'title' => 'GraphQl Downloadable Product Link',
    'type' => \Magento\Downloadable\Helper\Download::LINK_TYPE_URL,
    'is_shareable' => \Magento\Downloadable\Model\Link::LINK_SHAREABLE_CONFIG,
    'link_url' => 'http://example.com/downloadable.txt',
    'link_id' => 0,
    'is_delete' => null,
];
$link = $linkFactory->create(['data' => $linkData]);
$link->setId(null);
$link->setLinkType($linkData['type']);
$link->setStoreId($product->getStoreId());
$link->setWebsiteId($product->getStore()->getWebsiteId());
$link->setProductWebsiteIds($product->getWebsiteIds());
$link->setSortOrder(1);
$link->setPrice(0);
$link->setNumberOfDownloads(0);
$links[] = $link;
$extension = $product->getExtensionAttributes();
$extension->setDownloadableProductLinks($links);
$product->setExtensionAttributes($extension);
$productRepository = Bootstrap::getObjectManager()
    ->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
$productRepository->save($product);
