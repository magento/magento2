<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Eav\Model\Config;

require __DIR__ . '/product_varchar_attribute.php';
require __DIR__ . '/product_simple.php';

/** @var Config $eavConfig */
$eavConfig = $objectManager->create(Config::class);

$attributeCode = 'varchar_attribute';
/** @var ProductAttributeInterface $varcharAttribute */
$varcharAttribute = $attributeRepository->get($attributeCode);
$varcharAttribute->setDefaultValue('Varchar default value');
$attributeRepository->save($varcharAttribute);
$eavConfig->clear();

/** @var ProductInterface $simpleProduct */
$simpleProduct = $productRepository->get('simple');
$simpleProduct->setCustomAttribute($attributeCode, $attributeRepository->get($attributeCode)->getDefaultValue());
$productRepository->save($simpleProduct);
