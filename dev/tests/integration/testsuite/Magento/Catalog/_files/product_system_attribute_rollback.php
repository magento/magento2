<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Exception\NoSuchEntityException;

/** @var $attributeRepository \Magento\Catalog\Model\Product\Attribute\Repository */
$attributeRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->get(\Magento\Catalog\Model\Product\Attribute\Repository::class);

try {
    /** @var $attribute \Magento\Eav\Api\Data\AttributeInterface */
    $attribute = $attributeRepository->get('test_attribute_code_333');
    $attributeRepository->save($attribute->setIsUserDefined(1));
    // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
} catch (NoSuchEntityException $e) {
}
/** @var \Magento\Framework\Registry $registry */
$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

try {
    $attribute = $attributeRepository->get('test_attribute_code_333');
    if ($attribute->getId()) {
        $attribute->delete();
    }
    // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
} catch (\Exception $e) {
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
