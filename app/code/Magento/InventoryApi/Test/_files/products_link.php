<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Module\Manager;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryApi\Api\SourceItemsDeleteInterface;
use Magento\InventoryCatalog\Api\DefaultSourceProviderInterface;
use Magento\TestFramework\Helper\Bootstrap;

use Magento\Framework\Indexer\IndexerInterface;
use Magento\InventoryIndexer\Indexer\InventoryIndexer;

$objectManager = Bootstrap::getObjectManager();

/** @var  ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$productRepository->cleanCache();

$product = $productRepository->get('SKU-1');

$productLink1 = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Catalog\Api\Data\ProductLinkInterface::class
);
$productLink1->setSku('SKU-1');
$productLink1->setLinkedProductSku('SKU-2');
$productLink1->setPosition(1);
$productLink1->setLinkType('upsell');


$productLink2 = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Catalog\Api\Data\ProductLinkInterface::class
);
$productLink2->setSku('SKU-1');
$productLink2->setLinkedProductSku('SKU-3');
$productLink2->setPosition(2);
$productLink2->setLinkType('upsell');

$product->setProductLinks([$productLink1, $productLink2]);

$productRepository->save($product);
