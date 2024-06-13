<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_attribute.php');
/** @var $attributeRepository \Magento\Catalog\Model\Product\Attribute\Repository */
$attributeRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->get(\Magento\Catalog\Model\Product\Attribute\Repository::class);
/** @var $attribute \Magento\Eav\Api\Data\AttributeInterface */
$attribute = $attributeRepository->get('test_attribute_code_333');

$attributeRepository->save($attribute->setIsUserDefined(0));
