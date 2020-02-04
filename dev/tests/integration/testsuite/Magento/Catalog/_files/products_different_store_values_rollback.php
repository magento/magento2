<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Registry;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Exception\NoSuchEntityException;

/** @var Registry $registry */
$registry = Bootstrap::getObjectManager()->get(Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$repository = Bootstrap::getObjectManager()->create(
    ProductRepository::class
);

try {
    $product = $repository->get('store_name');
    $product->delete();
} catch (NoSuchEntityException $e) {
    //Entity already deleted
}

try {
    $product = $repository->get('store_description');
    $product->delete();
} catch (NoSuchEntityException $e) {
    //Entity already deleted
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);

require __DIR__ . '/../../Store/_files/core_fixturestore_rollback.php';

