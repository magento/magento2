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
    ->setName('GraphQl Downloadable Product (Links can be purchased separately)')
    ->setSku('graphql-downloadable-product-with-purchased-separately-links')
    ->setPrice(10)
    ->setVisibility(ProductVisibility::VISIBILITY_BOTH)
    ->setStatus(ProductStatus::STATUS_ENABLED)
    ->setStockData([
        'qty'          => 100,
        'is_in_stock'  => 1,
        'manage_stock' => 1,
    ]);

/**
 * @var \Magento\Downloadable\Api\Data\LinkInterfaceFactory $linkFactory1
 */
$linkFactory1 = Bootstrap::getObjectManager()
    ->get(\Magento\Downloadable\Api\Data\LinkInterfaceFactory::class);
$linkData = [
    'title'        => 'GraphQl Downloadable Product Link 1',
    'type'         => \Magento\Downloadable\Helper\Download::LINK_TYPE_URL,
    'is_shareable' => \Magento\Downloadable\Model\Link::LINK_SHAREABLE_CONFIG,
    'link_url'     => 'http://example.com/downloadable1.txt',
    'is_delete'    => null,
];
$link1 = $linkFactory1->create(['data' => $linkData]);
$link1->setLinkType($linkData['type'])
    ->setStoreId($product->getStoreId())
    ->setWebsiteId($product->getStore()->getWebsiteId())
    ->setProductWebsiteIds($product->getWebsiteIds())
    ->setSortOrder(1)
    ->setPrice(2.0000)
    ->setNumberOfDownloads(0);
/**
 * @var \Magento\Downloadable\Api\Data\LinkInterfaceFactory $linkFactory2
 */
$linkFactory2 = Bootstrap::getObjectManager()
    ->get(\Magento\Downloadable\Api\Data\LinkInterfaceFactory::class);
$linkData = [
    'title'        => 'GraphQl Downloadable Product Link 2',
    'type'         => \Magento\Downloadable\Helper\Download::LINK_TYPE_URL,
    'is_shareable' => \Magento\Downloadable\Model\Link::LINK_SHAREABLE_CONFIG,
    'link_url'     => 'http://example.com/downloadable2.txt',
    'is_delete'    => null,
];
$link2 = $linkFactory2->create(['data' => $linkData]);
$link2->setLinkType($linkData['type'])
    ->setStoreId($product->getStoreId())
    ->setWebsiteId($product->getStore()->getWebsiteId())
    ->setProductWebsiteIds($product->getWebsiteIds())
    ->setSortOrder(1)
    ->setPrice(4.0000)
    ->setNumberOfDownloads(0);
/**
 * @var \Magento\Downloadable\Api\Data\LinkInterfaceFactory $sampleFactory
 */
$sampleFactory = Bootstrap::getObjectManager()
    ->get(\Magento\Downloadable\Api\Data\LinkInterfaceFactory::class);
$sampleData = [
    'title' => 'Downloadable Product Sample',
    'sample' => [
        'type' => \Magento\Downloadable\Helper\Download::LINK_TYPE_URL,
        'url' => 'http://sampleUrl.com',
    ],
    'type' => \Magento\Downloadable\Helper\Download::LINK_TYPE_URL,
    'is_shareable' => \Magento\Downloadable\Model\Link::LINK_SHAREABLE_CONFIG,
    'link_url' => 'http://example.com/downloadable.txt',
    'is_delete' => null,
    'number_of_downloads' => 0,
    'price' => 0,
];
$sample1 = $sampleFactory->create(['data' => $sampleData]);
$sample1->setId(null);
$sample1->setSampleType($sampleData['sample']['type']);
$sample1->setSampleUrl($sampleData['sample']['url']);
$sample1->setLinkType($sampleData['type']);
$sample1->setStoreId($product->getStoreId());
$sample1->setWebsiteId($product->getStore()->getWebsiteId());
$sample1->setProductWebsiteIds($product->getWebsiteIds());
$sample1->setSortOrder(2);

$links = [$link1, $link2, $sample1];

$extension = $product->getExtensionAttributes();
$extension->setDownloadableProductLinks($links);

$product->setExtensionAttributes($extension);
$product->setLinksPurchasedSeparately(true);

$productRepository = Bootstrap::getObjectManager()
    ->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
$productRepository->save($product);
