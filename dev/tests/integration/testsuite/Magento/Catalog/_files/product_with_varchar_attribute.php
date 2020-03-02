<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Eav\Model\Config;

require __DIR__ . '/product_varchar_attribute.php';
require __DIR__ . '/product_simple.php';

/** @var Config $eavConfig */
$eavConfig = $objectManager->get(Config::class);
$eavConfig->clear();

$attribute->setDefaultValue('Varchar default value');
$attributeRepository->save($attribute);

$product->setCustomAttribute('varchar_attribute', $attribute->getDefaultValue());
$productRepository->save($product);
