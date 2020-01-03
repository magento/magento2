<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);
foreach (['simple_option_1', 'simple_option_2','simple_option_3', 'configurable'] as $sku) {
    try {
        $productRepository->deleteById($sku);
    } catch (NoSuchEntityException $e) {
        //Product already removed
    }
}

require __DIR__ . '/visual_swatch_attribute_with_different_options_type_rollback.php';

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
