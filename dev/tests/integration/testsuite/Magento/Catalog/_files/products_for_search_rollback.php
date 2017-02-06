<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

Bootstrap::getInstance()->getInstance()->reinitialize();

/** @var Registry $registry */
$registry = Bootstrap::getObjectManager()->get(Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var ProductRepositoryInterface $productRepository */
$productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);

$productSkus = ['search_product_1', 'search_product_2', 'search_product_3', 'search_product_4', 'search_product_5'];
foreach ($productSkus as $sku) {
    try {
        $product = $productRepository->get($sku, false, null, true);
        $productRepository->delete($product);
    } catch (NoSuchEntityException $e) {

    }
}

include_once 'category_rollback.php';

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
