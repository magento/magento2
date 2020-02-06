<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);
/** @var CategoryRepositoryInterface $categoryRepository */
$categoryRepository = $objectManager->get(CategoryRepositoryInterface::class);

try {
    $category = $categoryRepository->get(111);
    $categoryRepository->delete($category);
} catch (NoSuchEntityException $e) {
    //category already removed
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
