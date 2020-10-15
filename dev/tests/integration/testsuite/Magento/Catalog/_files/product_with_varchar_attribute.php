<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_varchar_attribute.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_simple.php');

$objectManager = Bootstrap::getObjectManager();
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
/** @var ProductAttributeRepositoryInterface $attributeRepository */
$attributeRepository = $objectManager->create(ProductAttributeRepositoryInterface::class);
$product = $productRepository->get('simple');
/** @var Config $eavConfig */
$eavConfig = $objectManager->get(Config::class);
$eavConfig->clear();
$attribute = $eavConfig->getAttribute(Product::ENTITY, 'varchar_attribute');
$attribute->setDefaultValue('Varchar default value');
$attributeRepository->save($attribute);

$product->setCustomAttribute('varchar_attribute', $attribute->getDefaultValue());
$productRepository->save($product);
