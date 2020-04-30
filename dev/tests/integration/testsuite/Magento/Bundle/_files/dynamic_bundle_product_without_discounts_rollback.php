<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Exception\NoSuchEntityException;

require __DIR__ . '/../../../Magento/Catalog/_files/category_with_different_price_products_rollback.php';

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

try {
    $product = $productRepository->get('dynamic_bundle_product_without_discounts', false, null, true);
    $productRepository->delete($product);
} catch (NoSuchEntityException $e) {
    //product already deleted.
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
