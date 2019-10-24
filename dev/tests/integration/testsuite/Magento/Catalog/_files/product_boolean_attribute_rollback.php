<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;

$objectManager = Bootstrap::getObjectManager();

$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);
/** @var Attribute $attribute */
$attribute = $objectManager->create(Attribute::class);
$attribute->load('boolean_attribute', 'attribute_code');
$attribute->delete();
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
