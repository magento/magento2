<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Setup\CategorySetup;
use Magento\CatalogInventory\Model\Stock\Status;
use Magento\Eav\Model\Config;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

$objectManager = Bootstrap::getObjectManager();

/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);

/** @var $installer CategorySetup */
$installer = $objectManager->create(CategorySetup::class);

/** @var Config $eavConfig */
$eavConfig = $objectManager->get(Config::class);
$firstAttribute = $eavConfig->getAttribute(Product::ENTITY, 'test_configurable_first');
$secondAttribute = $eavConfig->getAttribute(Product::ENTITY, 'test_configurable_second');

/** @var AttributeOptionInterface[] $firstAttributeOptions */
$firstAttributeOptions = $firstAttribute->getOptions();
/** @var AttributeOptionInterface[] $secondAttributeOptions */
$secondAttributeOptions = $secondAttribute->getOptions();

array_shift($firstAttributeOptions);
array_shift($secondAttributeOptions);
foreach ($firstAttributeOptions as $i => $firstAttributeOption) {
    foreach ($secondAttributeOptions as $j => $secondAttributeOption) {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $objectManager->get(ProductRepositoryInterface::class);
        try {
            //delete child product
            $sku = 'simple_' . $firstAttributeOption->getValue() . '_' . $secondAttributeOption->getValue();
            $product = $productRepository->get($sku, true);
            $stockStatus = $objectManager->create(Status::class);
            $stockStatus->load($product->getEntityId(), 'product_id');
            $stockStatus->delete();
            $productRepository->delete($product);
        } catch (NoSuchEntityException $e) {
            //Product already removed
        }
    }
}

//delete configurable product
try {
    $product = $productRepository->get('configurable_12345', true);
    $stockStatus = $objectManager->create(Status::class);
    $stockStatus->load($product->getEntityId(), 'product_id');
    $stockStatus->delete();

    $productRepository->delete($product);
} catch (NoSuchEntityException $e) {
    //Product already removed
}
Resolver::getInstance()->requireDataFixture(
    'Magento/ConfigurableProduct/_files/configurable_attribute_first_rollback.php'
);
Resolver::getInstance()->requireDataFixture(
    'Magento/ConfigurableProduct/_files/configurable_attribute_second_rollback.php'
);

Resolver::getInstance()->requireDataFixture(
    'Magento/Catalog/_files/product_image_rollback.php'
);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
