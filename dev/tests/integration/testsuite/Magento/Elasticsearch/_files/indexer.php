<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\App\MutableScopeConfig;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/../../../Magento/Catalog/_files/product_boolean_attribute.php';

/** @var $objectManager \Magento\Framework\ObjectManagerInterface */
$objectManager = Bootstrap::getObjectManager();

/** @var StoreManagerInterface $storeManager */
$storeManager = $objectManager->get(StoreManagerInterface::class);
/** @var Store $store */
$store = $objectManager->create(Store::class);
$storeCode = 'secondary';

if (!$store->load($storeCode)->getId()) {
    $store->setCode($storeCode)
        ->setWebsiteId($storeManager->getWebsite()->getId())
        ->setGroupId($storeManager->getWebsite()->getDefaultGroupId())
        ->setName('Secondary Store View')
        ->setSortOrder(10)
        ->setIsActive(1);
    $store->save();

    /** @var MutableScopeConfig $scopeConfig */
    $scopeConfig = $objectManager->get(MutableScopeConfig::class);
    $scopeConfig->setValue(
        'general/locale/code',
        'de_DE',
        ScopeInterface::SCOPE_STORES,
        $store->getId()
    );
}

$productRepository = $objectManager->get(ProductRepositoryInterface::class);
try {
    $productRepository->get('fulltext-1');
} catch (NoSuchEntityException $e) {
    /** @var $productFirst Product */
    $productFirst = $objectManager->create(Product::class);
    $productFirst->setTypeId('simple')
        ->setAttributeSetId(4)
        ->setWebsiteIds([1])
        ->setName('Simple Product Apple')
        ->setSku('fulltext-1')
        ->setPrice(10)
        ->setMetaTitle('first meta title')
        ->setMetaKeyword('first meta keyword')
        ->setMetaDescription('first meta description')
        ->setVisibility(Visibility::VISIBILITY_BOTH)
        ->setStatus(Status::STATUS_ENABLED)
        ->setStockData(['use_config_manage_stock' => 0])
        ->setBooleanAttribute(1)
        ->save();
}

try {
    $productRepository->get('fulltext-2');
} catch (NoSuchEntityException $e) {
    /** @var $productSecond Product */
    $productSecond = $objectManager->create(Product::class);
    $productSecond->setTypeId('simple')
        ->setAttributeSetId(4)
        ->setWebsiteIds([1])
        ->setName('Simple Product Banana')
        ->setSku('fulltext-2')
        ->setPrice(20)
        ->setMetaTitle('second meta title')
        ->setMetaKeyword('second meta keyword')
        ->setMetaDescription('second meta description')
        ->setVisibility(Visibility::VISIBILITY_BOTH)
        ->setStatus(Status::STATUS_ENABLED)
        ->setStockData(['use_config_manage_stock' => 0])
        ->setBooleanAttribute(1)
        ->save();
}

try {
    $productRepository->get('fulltext-3');
} catch (NoSuchEntityException $e) {
    /** @var $productThird Product */
    $productThird = $objectManager->create(Product::class);
    $productThird->setTypeId('simple')
        ->setAttributeSetId(4)
        ->setWebsiteIds([1])
        ->setName('Simple Product Orange')
        ->setSku('fulltext-3')
        ->setPrice(20)
        ->setMetaTitle('third meta title')
        ->setMetaKeyword('third meta keyword')
        ->setMetaDescription('third meta description')
        ->setVisibility(Visibility::VISIBILITY_BOTH)
        ->setStatus(Status::STATUS_ENABLED)
        ->setStockData(['use_config_manage_stock' => 0])
        ->setBooleanAttribute(1)
        ->save();
}

try {
    $productRepository->get('fulltext-4');
} catch (NoSuchEntityException $e) {
    /** @var $productFourth Product */
    $productFourth = $objectManager->create(Product::class);
    $productFourth->setTypeId('simple')
        ->setAttributeSetId(4)
        ->setWebsiteIds([1])
        ->setName('Simple Product Papaya')
        ->setSku('fulltext-4')
        ->setPrice(20)
        ->setMetaTitle('fourth meta title')
        ->setMetaKeyword('fourth meta keyword')
        ->setMetaDescription('fourth meta description')
        ->setVisibility(Visibility::VISIBILITY_BOTH)
        ->setStatus(Status::STATUS_ENABLED)
        ->setStockData(['use_config_manage_stock' => 0])
        ->setBooleanAttribute(0)
        ->save();
}

try {
    $productRepository->get('fulltext-5');
} catch (NoSuchEntityException $e) {
    /** @var $productFifth Product */
    $productFifth = $objectManager->create(Product::class);
    $productFifth->setTypeId('simple')
        ->setAttributeSetId(4)
        ->setWebsiteIds([1])
        ->setName('Simple Product Cherry')
        ->setSku('fulltext-5')
        ->setPrice(20)
        ->setMetaTitle('fifth meta title')
        ->setMetaKeyword('fifth meta keyword')
        ->setMetaDescription('fifth meta description')
        ->setVisibility(Visibility::VISIBILITY_BOTH)
        ->setStatus(Status::STATUS_ENABLED)
        ->setStockData(['use_config_manage_stock' => 0])
        ->setBooleanAttribute(0)
        ->save();
}
