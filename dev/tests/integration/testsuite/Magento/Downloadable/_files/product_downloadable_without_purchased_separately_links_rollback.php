<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Downloadable\Api\DomainManagerInterface;

/** @var DomainManagerInterface $domainManager */
$domainManager = Bootstrap::getObjectManager()->get(DomainManagerInterface::class);
$domainManager->removeDomains(['example.com']);

/** @var \Magento\Framework\Registry $registry */
$registry = Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$productRepository = Bootstrap::getObjectManager()
    ->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);

try {
    $product = $productRepository->get(
        'downloadable-product-without-purchased-separately-links',
        false,
        null,
        true
    );
    $productRepository->delete($product);
} catch (NoSuchEntityException $e) {
    // Tests which are wrapped with MySQL transaction clear all data by transaction rollback
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
