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
$product
    ->setTypeId(ProductType::TYPE_DOWNLOADABLE)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('GraphQl Downloadable Product (Links can not be purchased separately)')
    ->setSku('graphql-downloadable-product-without-purchased-separately-links')
    ->setPrice(10)
    ->setVisibility(ProductVisibility::VISIBILITY_BOTH)
    ->setStatus(ProductStatus::STATUS_ENABLED)
    ->setStockData([
        'qty'          => 100,
        'is_in_stock'  => 1,
        'manage_stock' => 1,
    ]);

/**
 * @var \Magento\Downloadable\Api\Data\LinkInterfaceFactory $linkFactory
 */
$linkFactory = Bootstrap::getObjectManager()
    ->get(\Magento\Downloadable\Api\Data\LinkInterfaceFactory::class);
$linkData = [
    'title'        => 'GraphQl Downloadable Product Link',
    'type'         => \Magento\Downloadable\Helper\Download::LINK_TYPE_URL,
    'is_shareable' => \Magento\Downloadable\Model\Link::LINK_SHAREABLE_CONFIG,
    'link_url'     => 'http://example.com/downloadable.txt',
    'is_delete'    => null,
];
$link = $linkFactory->create(['data' => $linkData]);
$link->setLinkType($linkData['type'])
    ->setStoreId($product->getStoreId())
    ->setWebsiteId($product->getStore()->getWebsiteId())
    ->setProductWebsiteIds($product->getWebsiteIds())
    ->setSortOrder(1)
    ->setPrice(0.0000)
    ->setNumberOfDownloads(0);
$links   = [];
$links[] = $link;

$extension = $product->getExtensionAttributes();
$extension->setDownloadableProductLinks($links);

$product->setExtensionAttributes($extension);
$product->setLinksPurchasedSeparately(false);

$productRepository = Bootstrap::getObjectManager()
    ->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
$productRepository->save($product);
