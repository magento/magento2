<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Registry;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var CategoryRepositoryInterface $categoryRepository */
$categoryRepository = $objectManager->get(CategoryRepositoryInterface::class);

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);

/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);

$isSecurePreviousValue = $registry->registry('isSecureArea');

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$productRepository->deleteById('simple116');
$productRepository->cleanCache();

$categoryRepository->deleteByIdentifier(113);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', $isSecurePreviousValue);
