<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var \Magento\Framework\Registry $registry */
$registry = $objectManager->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$productRepository = Bootstrap::getObjectManager()
    ->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);

$productSkus = ['simple_not_avalilable_1110', 'simple_not_avalilable_1120', 'configurable_not_available'];
/** @var SearchCriteriaBuilder $searchCriteriaBuilder */
$searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
$searchCriteriaBuilder->addFilter(ProductInterface::SKU, $productSkus, 'in');
$result = $productRepository->getList($searchCriteriaBuilder->create());
foreach ($result->getItems() as $product) {
    $productRepository->delete($product);
}

require __DIR__ . '/../../../Magento/Framework/Search/_files/configurable_attribute_rollback.php';

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
