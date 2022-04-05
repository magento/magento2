<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Eav\Model\Config;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$attributeRepository = $objectManager->get(ProductAttributeRepositoryInterface::class);
try {
    $attribute = $attributeRepository->get('test_searchable_attribute');
    $attributeRepository->delete($attribute);
} catch (NoSuchEntityException $e) {
    //Attribute already removed
}
$eavConfig = $objectManager->get(Config::class);
$eavConfig->clear();

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
