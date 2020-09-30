<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Data;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Observer\SwitchPriceAttributeScopeOnConfigChange;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Store/_files/second_website_with_store_group_and_store.php');

$objectManager = Bootstrap::getObjectManager();
/** @var Config $configResource */
$configResource = $objectManager->get(Config::class);
$configResource->saveConfig(Data::XML_PATH_PRICE_SCOPE, Store::PRICE_SCOPE_WEBSITE, 'default', 0);
$objectManager->get(ReinitableConfigInterface::class)->reinit();
/** @var SwitchPriceAttributeScopeOnConfigChange $observer */
$observer = $objectManager->get(Observer::class);
$objectManager->get(SwitchPriceAttributeScopeOnConfigChange::class)->execute($observer);
/** @var ProductInterfaceFactory $productFactory */
$productFactory = $objectManager->get(ProductInterfaceFactory::class);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
/** @var WebsiteRepositoryInterface $websiteRepository */
$websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
$websiteId = $websiteRepository->get('test')->getId();
$defaultWebsiteId = $websiteRepository->get('base')->getId();
/** @var StoreManagerInterface $storeManager */
$storeManager = $objectManager->get(StoreManagerInterface::class);
$secondStoreId = $storeManager->getStore('fixture_second_store')->getId();
/** @var $product \Magento\Catalog\Model\Product */
$product = $productFactory->create();
$product->setTypeId(Type::TYPE_SIMPLE)
    ->setAttributeSetId($product->getDefaultAttributeSetId())
    ->setWebsiteIds([$defaultWebsiteId, $websiteId])
    ->setName('Second website price product')
    ->setSku('second-website-price-product')
    ->setPrice(20)
    ->setSpecialPrice(15)
    ->setWeight(1)
    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(
        [
            'use_config_manage_stock' => 1,
            'qty' => 100,
            'is_in_stock' => 1
        ]
    );
$productRepository->save($product);

try {
    $currentStoreCode = $storeManager->getStore()->getCode();
    $storeManager->setCurrentStore('fixture_second_store');
    $product = $productRepository->get('second-website-price-product', false, $secondStoreId, true);
    $product->setPrice(10)
        ->setSpecialPrice(5.99);
    $productRepository->save($product);
} finally {
    $storeManager->setCurrentStore($currentStoreCode);
}
