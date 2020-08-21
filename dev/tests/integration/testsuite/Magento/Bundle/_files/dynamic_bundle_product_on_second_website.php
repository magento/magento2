<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Data;
use Magento\Catalog\Helper\DefaultCategory;
use Magento\Catalog\Observer\SwitchPriceAttributeScopeOnConfigChange;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Catalog\Model\GetCategoryByName;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/dynamic_bundle_product_with_special_price.php';
require __DIR__ . '/../../Store/_files/second_website_with_store_group_and_store.php';

$objectManager = Bootstrap::getObjectManager();
/** @var Config $configResource */
$configResource = $objectManager->get(Config::class);
$configResource->saveConfig(Data::XML_PATH_PRICE_SCOPE, Store::PRICE_SCOPE_WEBSITE, 'default', 0);
$objectManager->get(ReinitableConfigInterface::class)->reinit();
/** @var SwitchPriceAttributeScopeOnConfigChange $observer */
$observer = $objectManager->get(Observer::class);
$objectManager->get(SwitchPriceAttributeScopeOnConfigChange::class)->execute($observer);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$productRepository->cleanCache();
/** @var WebsiteRepositoryInterface $websiteRepository */
$websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
$websiteId = $websiteRepository->get('test')->getId();
$defaultWebsiteId = $websiteRepository->get('base')->getId();
/** @var StoreManagerInterface $storeManager */
$storeManager = $objectManager->get(StoreManagerInterface::class);
$secondStoreId = $storeManager->getStore('fixture_second_store')->getId();
/** @var DefaultCategory $categoryHelper */
$categoryHelper = $objectManager->get(DefaultCategory::class);
/** @var GetCategoryByName $getCategoryByName */
$getCategoryByName = $objectManager->get(GetCategoryByName::class);

$product = $productRepository->get('dynamic_bundle_product_with_special_price', false, null, true);
$product->setWebsiteIds([$defaultWebsiteId, $websiteId])
    ->setCategoryIds([$categoryHelper->getId(), $getCategoryByName->execute('Category 999')->getId()]);
$productRepository->save($product);

try {
    $currentStoreCode = $storeManager->getStore()->getCode();
    $storeManager->setCurrentStore('fixture_second_store');
    $firstSimple = $productRepository->get('simple1000', false, $secondStoreId, true);
    $firstSimple->setPrice(20)
        ->setSpecialPrice(10)
        ->setWebsiteIds([$defaultWebsiteId, $websiteId]);
    $productRepository->save($firstSimple);
    $secondSimple = $productRepository->get('simple1001', false, $secondStoreId, true);
    $secondSimple->setPrice(40)
        ->setSpecialPrice(30)
        ->setWebsiteIds([$defaultWebsiteId, $websiteId]);
    $productRepository->save($secondSimple);
} finally {
    $storeManager->setCurrentStore($currentStoreCode);
}
