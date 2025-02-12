<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var Attribute $attribute */

use Magento\Catalog\Model\Category\AttributeFactory;
use Magento\Catalog\Model\Category\Attribute;
use Magento\TestFramework\Helper\Bootstrap;

$attributeFactory = Bootstrap::getObjectManager()->get(AttributeFactory::class);
$attribute = $attributeFactory->create();
$attribute->setAttributeCode('short_description')
    ->setEntityTypeId(3)
    ->setBackendType('text')
    ->setFrontendInput('textarea')
    ->setIsGlobal(1)
    ->setIsUserDefined(1);
$attribute->save();
