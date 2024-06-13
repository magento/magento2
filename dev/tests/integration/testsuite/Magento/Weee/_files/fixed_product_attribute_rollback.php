<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/* @var \Magento\Eav\Model\Entity\Attribute $attribute */
$attribute = $objectManager->get(\Magento\Eav\Model\Entity\Attribute::class);
$attribute->loadByCode(\Magento\Catalog\Model\Product::ENTITY, 'fixed_product_attribute');
$attribute->delete();
