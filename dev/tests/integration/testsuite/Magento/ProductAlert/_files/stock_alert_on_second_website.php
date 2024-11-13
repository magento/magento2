<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\ProductAlert\Model\ResourceModel\Stock as StockResource;
use Magento\ProductAlert\Model\StockFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Customer/_files/customer_for_second_website_with_address.php');

$objectManager = Bootstrap::getObjectManager();
/** @var StoreManagerInterface $storeManager */
$storeManager = $objectManager->get(StoreManagerInterface::class);
$secondWebsite = $storeManager->getWebsite('test');
/** @var ProductInterfaceFactory $productFactory */
$productFactory = $objectManager->get(ProductInterfaceFactory::class);
/** @var ProductRepositoryInterface $peoductRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
/** @var StockFactory $stockFactory */
$stockFactory = $objectManager->get(StockFactory::class);
/** @var StockResource $stockResource */
$stockResource = $objectManager->get(StockResource::class);
/** @var CustomerRepositoryInterface $customerRepository */
$customerRepository = $objectManager->get(CustomerRepositoryInterface::class);
$customer = $customerRepository->get('customer_second_ws_with_addr@example.com', (int)$secondWebsite->getId());


$product = $productFactory->create();
$product
    ->setTypeId('simple')
    ->setAttributeSetId(4)
    ->setWebsiteIds([(int)$secondWebsite->getId()])
    ->setName('Simple Product2')
    ->setSku('simple_on_second_website')
    ->setPrice(10)
    ->setMetaTitle('meta title2')
    ->setMetaKeyword('meta keyword2')
    ->setMetaDescription('meta description2')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(['is_in_stock' => StockStatusInterface::STATUS_OUT_OF_STOCK]);

$productRepository->save($product);

$stockAlert = $stockFactory->create();
$stockAlert->setCustomerId(
    $customer->getId()
)->setProductId(
    (int)$productRepository->get($product->getSku())->getId()
)->setWebsiteId(
    (int)$secondWebsite->getId()
)->setStoreId(
    (int)$storeManager->getStore('fixture_third_store')->getId()
);
$stockResource->save($stockAlert);
