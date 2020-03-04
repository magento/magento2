<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductExtensionInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\ConfigurableProduct\Helper\Product\Options\Factory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/configurable_attribute.php';
$objectManager = Bootstrap::getObjectManager();
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
/** @var ProductFactory $productFactory */
$productFactory = $objectManager->get(ProductFactory::class);
/** @var Factory $optionsFactory */
$optionsFactory = $objectManager->get(Factory::class);
/** @var  ProductExtensionInterfaceFactory $productExtensionAttributes */
$productExtensionAttributesFactory = $objectManager->get(ProductExtensionInterfaceFactory::class);
/** @var WebsiteRepositoryInterface $websiteRepository */
$websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
$defaultWebsiteId = $websiteRepository->get('base')->getId();

$option = $attribute->getSource()->getOptionId('Option 1');
$product = $productFactory->create();
$product->setTypeId(Type::TYPE_SIMPLE)
    ->setAttributeSetId($product->getDefaultAttributeSetId())
    ->setWebsiteIds([$defaultWebsiteId])
    ->setName('Configurable Option 1')
    ->setSku('simple_1')
    ->setPrice(10.00)
    ->setTestConfigurable($option)
    ->setVisibility(Visibility::VISIBILITY_NOT_VISIBLE)
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1]);
$product = $productRepository->save($product);

$configurableOptions = $optionsFactory->create(
    [
        [
            'attribute_id' => $attribute->getId(),
            'code' => $attribute->getAttributeCode(),
            'label' => $attribute->getStoreLabel(),
            'position' => '0',
            'values' => [['label' => 'test', 'attribute_id' => $attribute->getId(), 'value_index' => $option]],
        ],
    ]
);
$extensionConfigurableAttributes = $product->getExtensionAttributes() ?: $productExtensionAttributesFactory->create();
$extensionConfigurableAttributes->setConfigurableProductOptions($configurableOptions);
$extensionConfigurableAttributes->setConfigurableProductLinks([$product->getId()]);

$configurableProduct = $productFactory->create();
$configurableProduct->setExtensionAttributes($extensionConfigurableAttributes);
$configurableProduct->setTypeId(Configurable::TYPE_CODE)
    ->setAttributeSetId($configurableProduct->getDefaultAttributeSetId())
    ->setWebsiteIds([$defaultWebsiteId])
    ->setName('Configurable Product')
    ->setSku('configurable')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'is_in_stock' => 1]);
$productRepository->save($configurableProduct);
