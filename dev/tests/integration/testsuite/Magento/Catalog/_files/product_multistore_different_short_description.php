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
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Store/_files/core_fixturestore.php');

$objectManager = Bootstrap::getObjectManager();
/** @var StoreManagerInterface $storeManager */
$storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
/** @var ProductFactory $productFactory */
$productFactory = $objectManager->get(ProductFactory::class);

$currentStoreId = $storeManager->getStore()->getId();
$secondStoreId = $storeManager->getStore('fixturestore')->getId();

$product = $productFactory->create();
$product->setTypeId(Type::TYPE_SIMPLE)
    ->setAttributeSetId($product->getDefaultAttributeSetId())
    ->setWebsiteIds([$storeManager->getWebsite(true)->getId()])
    ->setName('Simple Product One')
    ->setSku('simple-different-short-description')
    ->setPrice(10)
    ->setWeight(18)
    ->setStockData(['use_config_manage_stock' => 0])
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setShortDescription('First store view short description')
    ->setStatus(Status::STATUS_ENABLED);
$productRepository->save($product);

try {
    $storeManager->setCurrentStore($secondStoreId);
    $product = $productRepository->get('simple-different-short-description', false, $secondStoreId);
    $product->setShortDescription('Second store view short description');
    $productRepository->save($product);
} finally {
    $storeManager->setCurrentStore($currentStoreId);
}
