<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\TestFramework\Helper\Bootstrap;

$rootSku = 'simple-related-%';
$linkedSku = 'related-product-%';
/** @var ProductRepository $repo */
$repo = Bootstrap::getObjectManager()->get(ProductRepository::class);
/** @var SearchCriteriaBuilder $criteriaBuilder */
$criteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
$listToDelete = $repo->getList($criteriaBuilder->addFilter('sku', $rootSku, 'like')->create());
foreach ($listToDelete->getItems() as $item) {
    try {
        $repo->delete($item);
    } catch (\Throwable $exception) {
        //Could be deleted before
    }
}
$listToDelete = $repo->getList($criteriaBuilder->addFilter('sku', $linkedSku, 'like')->create());
foreach ($listToDelete->getItems() as $item) {
    try {
        $repo->delete($item);
    } catch (\Throwable $exception) {
        //Could be deleted before
    }
}
