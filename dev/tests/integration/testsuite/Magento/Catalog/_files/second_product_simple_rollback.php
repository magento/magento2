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
use Magento\UrlRewrite\Model\UrlRewrite;

$objectManager = Bootstrap::getObjectManager();
/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$productRepository->cleanCache();

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

try {
    $productRepository->deleteById('simple2');
} catch (NoSuchEntityException $e) {
    //Product already removed
}

$urlRewrite = $objectManager->create(UrlRewrite::class);
$urlRewrite->load('simple2.html', 'request_path');
$urlRewrite->delete();

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
