<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductExtensionFactory;
use Magento\Catalog\Helper\Data;
use Magento\Catalog\Helper\DefaultCategory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Observer\SwitchPriceAttributeScopeOnConfigChange;
use Magento\Config\Model\ResourceModel\Config;
use Magento\ConfigurableProduct\Helper\Product\Options\Factory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/../../Store/_files/second_website_with_store_group_and_store.php';
require __DIR__ . '/configurable_attribute.php';
require __DIR__ . '/../../Catalog/_files/category.php';

$objectManager = Bootstrap::getObjectManager();
/** @var ProductAttributeRepositoryInterface $productAttributeRepository */
$productAttributeRepository = $objectManager->get(ProductAttributeRepositoryInterface::class);
/** @var WebsiteRepositoryInterface $websiteRepository */
$websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
/** @var ProductFactory $productFactory */
$productFactory = $objectManager->get(ProductFactory::class);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$productRepository->cleanCache();
/** @var Factory $optionsFactory */
$optionsFactory = $objectManager->get(Factory::class);
/** @var ProductExtensionFactory $extensionAttributesFactory */
$extensionAttributesFactory = $objectManager->get(ProductExtensionFactory::class);
/** @var Config $configResource */
$configResource = $objectManager->get(Config::class);
/** @var SwitchPriceAttributeScopeOnConfigChange $observer */
$observer = $objectManager->get(Observer::class);
/** @var DefaultCategory $categoryHelper */
$categoryHelper = $objectManager->get(DefaultCategory::class);

$attribute = $productAttributeRepository->get('test_configurable');
$options = $attribute->getOptions();
$baseWebsite = $websiteRepository->get('base');
$secondWebsite = $websiteRepository->get('test');
$attributeValues = [];
$associatedProductIds = [];
array_shift($options);

foreach ($options as $option) {
    $product = $productFactory->create();
    $product->setTypeId(ProductType::TYPE_SIMPLE)
        ->setAttributeSetId($product->getDefaultAttributeSetId())
        ->setWebsiteIds([$baseWebsite->getId(), $secondWebsite->getId()])
        ->setName('Configurable Option ' . $option->getLabel())
        ->setSku(strtolower(str_replace(' ', '_', 'simple ' . $option->getLabel())))
        ->setTestConfigurable($option->getValue())
        ->setVisibility(Visibility::VISIBILITY_NOT_VISIBLE)
        ->setStatus(Status::STATUS_ENABLED)
        ->setPrice(150)
        ->setCategoryIds([$categoryHelper->getId(), 333])
        ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1]);
    $product = $productRepository->save($product);
    $associatedProductIds[] = $product->getId();
    $attributeValues[] = [
        'label' => 'test',
        'attribute_id' => $attribute->getId(),
        'value_index' => $option->getValue(),
    ];
}
$configurableAttributesData = [
    [
        'values' => $attributeValues,
        'attribute_id' => $attribute->getId(),
        'code' => $attribute->getAttributeCode(),
        'label' => $attribute->getStoreLabel(),
        'position' => '0',
    ],
];
$configurableOptions = $optionsFactory->create($configurableAttributesData);

$product = $productFactory->create();
$extensionConfigurableAttributes = $product->getExtensionAttributes() ?: $extensionAttributesFactory->create();
$extensionConfigurableAttributes->setConfigurableProductOptions($configurableOptions);
$extensionConfigurableAttributes->setConfigurableProductLinks($associatedProductIds);
$product->setExtensionAttributes($extensionConfigurableAttributes);
$product->setTypeId(Configurable::TYPE_CODE)
    ->setAttributeSetId($product->getDefaultAttributeSetId())
    ->setWebsiteIds([$baseWebsite->getId(), $secondWebsite->getId()])
    ->setStatus(Status::STATUS_ENABLED)
    ->setCategoryIds([$categoryHelper->getId(), 333])
    ->setSku('configurable')
    ->setName('Configurable Product')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStockData(['use_config_manage_stock' => 1, 'is_in_stock' => 1]);
$productRepository->save($product);

$configResource->saveConfig(Data::XML_PATH_PRICE_SCOPE, Store::PRICE_SCOPE_WEBSITE, 'default', 0);
$objectManager->get(ReinitableConfigInterface::class)->reinit();
$objectManager->get(SwitchPriceAttributeScopeOnConfigChange::class)->execute($observer);
/** @var StoreManagerInterface $storeManager */
$storeManager = $objectManager->get(StoreManagerInterface::class);
$secondStoreId = $storeManager->getStore('fixture_second_store')->getId();

try {
    $currentStoreCode = $storeManager->getStore()->getCode();
    $storeManager->setCurrentStore('fixture_second_store');
    $firstChild = $productRepository->get('simple_option_1', false, $secondStoreId, true);
    $firstChild->setPrice(20)
        ->setSpecialPrice(10);
    $productRepository->save($firstChild);
    $secondChild = $productRepository->get('simple_option_2', false, $secondStoreId, true);
    $secondChild->setPrice(40)
        ->setSpecialPrice(30);
    $productRepository->save($secondChild);
} finally {
    $storeManager->setCurrentStore($currentStoreCode);
}
