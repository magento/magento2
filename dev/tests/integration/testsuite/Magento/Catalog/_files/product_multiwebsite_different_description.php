<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ProductFactory;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Store/_files/website.php');

$objectManager =  Bootstrap::getObjectManager();
/** @var ProductFactory $productFactory */
$productFactory = $objectManager->create(ProductFactory::class);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
/** @var WebsiteRepositoryInterface $websiteRepository */
$websiteRepository = $objectManager->create(WebsiteRepositoryInterface::class);
$websiteId = $websiteRepository->get('test')->getId();
$defaultWebsiteId = $websiteRepository->get('base')->getId();

$product = $productFactory->create();
$product->setTypeId(Type::TYPE_SIMPLE)
    ->setAttributeSetId($product->getDefaultAttributeSetId())
    ->setWebsiteIds([$defaultWebsiteId, $websiteId])
    ->setName('Simple Product on two websites')
    ->setSku('simple-on-two-websites-different-description')
    ->setPrice(10)
    ->setDescription('<p>Product base description</p>');

$productRepository->save($product);
$product = $productRepository->get('simple-on-two-websites-different-description');
$product->setDescription('<p>Product second description</p>');
$productRepository->save($product);
