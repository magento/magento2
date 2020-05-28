<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Store/_files/second_website_with_two_stores.php');

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
    ->setSku('simple-on-two-websites')
    ->setPrice(10)
    ->setDescription('Description')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1]);

$productRepository->save($product);
