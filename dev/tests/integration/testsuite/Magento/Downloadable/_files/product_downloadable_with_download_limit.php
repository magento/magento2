<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Downloadable\Api\Data\LinkInterface;
use Magento\Downloadable\Api\LinkRepositoryInterface;
use Magento\Downloadable\Helper\Download;
use Magento\Downloadable\Model\Link;
use Magento\Downloadable\Model\Product\Type;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Downloadable\Api\DomainManagerInterface;

/** @var DomainManagerInterface $domainManager */
$domainManager = Bootstrap::getObjectManager()->get(DomainManagerInterface::class);
$domainManager->addDomains(['example.com']);

/** @var ProductRepositoryInterface $productRepository */
$productRepository = Bootstrap::getObjectManager()
    ->get(ProductRepositoryInterface::class);
/** @var LinkRepositoryInterface $linkRepository */
$linkRepository = Bootstrap::getObjectManager()
    ->create(LinkRepositoryInterface::class);
/** @var ProductInterface $product */
$product = Bootstrap::getObjectManager()
    ->create(ProductInterface::class);
/** @var LinkInterface $downloadableProductLink */
$downloadableProductLink = Bootstrap::getObjectManager()
    ->create(LinkInterface::class);

$downloadableProductLink
//    ->setId(null)
    ->setLinkType(Download::LINK_TYPE_URL)
    ->setTitle('Downloadable Product Link')
    ->setIsShareable(Link::LINK_SHAREABLE_CONFIG)
    ->setLinkUrl('http://example.com/downloadable.txt')
    ->setNumberOfDownloads(100)
    ->setSortOrder(1)
    ->setPrice(0);

$downloadableProductLinks[] = $downloadableProductLink;

$product
    ->setId(1)
    ->setTypeId(Type::TYPE_DOWNLOADABLE)
    ->setExtensionAttributes(
        $product->getExtensionAttributes()
            ->setDownloadableProductLinks($downloadableProductLinks)
    )
    ->setSku('downloadable-product')
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Downloadable Product Limited')
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

$productRepository->save($product);
