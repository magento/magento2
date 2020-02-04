<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

// phpcs:ignore Magento2.Security.IncludeFile
require __DIR__ . '/product_attribute.php';
/** @var $attributeRepository \Magento\Catalog\Model\Product\Attribute\Repository */
$attributeRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->get(\Magento\Catalog\Model\Product\Attribute\Repository::class);
/** @var $attribute \Magento\Eav\Api\Data\AttributeInterface */
$attribute = $attributeRepository->get('test_attribute_code_333');

$attributeRepository->save($attribute->setIsUserDefined(0));
