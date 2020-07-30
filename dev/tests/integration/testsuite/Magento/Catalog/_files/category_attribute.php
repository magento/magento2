<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */

use Magento\Catalog\Model\Category\Attribute;
use Magento\TestFramework\Helper\Bootstrap;

$attribute = Bootstrap::getObjectManager()
    ->create(Attribute::class);
$attribute->setAttributeCode('test_attribute_code_666')
    ->setEntityTypeId(3)
    ->setIsGlobal(1)
    ->setIsUserDefined(1);
$attribute->save();
