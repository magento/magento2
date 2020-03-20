<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Downloadable\Api\DomainManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Downloadable\Api\Data\LinkInterfaceFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Downloadable\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Downloadable\Helper\Download;
use Magento\Downloadable\Model\Link;
use Magento\Downloadable\Api\Data\SampleInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Downloadable\Api\Data\LinkInterface;

$objectManager = Bootstrap::getObjectManager();

$storeManager = $objectManager->get(StoreManagerInterface::class);
$storeManager->setCurrentStore($storeManager->getStore('admin')->getId());

$domainManager = $objectManager->get(DomainManagerInterface::class);
$domainManager->addDomains(
    [
        'example.com',
        'www.example.com',
        'www.sample.example.com',
        'google.com'
    ]
);

$product = $objectManager->get(ProductInterface::class);
$product
    ->setTypeId(Type::TYPE_DOWNLOADABLE)
    ->setId(1)
    ->setAttributeSetId(4)
    ->setName('Downloadable Product')
    ->setSku('downloadable-product')
    ->setPrice(10)
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setLinksPurchasedSeparately(true)
    ->setStockData(
        [
            'qty' => 100,
            'is_in_stock' => 1,
            'manage_stock' => 1,
        ]
    );

$linkFactory = $objectManager->get(LinkInterfaceFactory::class);
/** @var LinkInterface $link */
$link = $linkFactory->create();
$link->setTitle('Downloadable Product Link');
$link->setIsShareable(Link::LINK_SHAREABLE_CONFIG);
$link->setLinkUrl('http://example.com/downloadable.txt');
$link->setLinkType(Download::LINK_TYPE_URL);
$link->setStoreId($product->getStoreId());
$link->setWebsiteId($product->getStore()->getWebsiteId());
$link->setProductWebsiteIds($product->getWebsiteIds());
$link->setSortOrder(1);
$link->setPrice(0);
$link->setNumberOfDownloads(0);

$sampleFactory = $objectManager->get(SampleInterfaceFactory::class);
$sample = $sampleFactory->create();
$sample->setTitle('Downloadable Product Sample')
    ->setSampleType(Download::LINK_TYPE_URL)
    ->setSampleUrl('http://example.com/downloadable.txt')
    ->setStoreId($product->getStoreId())
    ->setWebsiteId($product->getStore()->getWebsiteId())
    ->setProductWebsiteIds($product->getWebsiteIds())
    ->setSortOrder(10);

$extension = $product->getExtensionAttributes();
$extension->setDownloadableProductLinks([$link]);
$extension->setDownloadableProductSamples([$sample]);
$product->setExtensionAttributes($extension);

$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$productRepository->save($product);
