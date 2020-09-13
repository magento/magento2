<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

$objectManager = Bootstrap::getObjectManager();

/** @var \Magento\Framework\Registry $registry */
$registry = $objectManager->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$productRepository = Bootstrap::getObjectManager()
    ->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);

$productSkus = ['configurable_option_single_child', 'configurable_with_single_child'];
/** @var SearchCriteriaBuilder $searchCriteriaBuilder */
$searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
$searchCriteriaBuilder->addFilter(ProductInterface::SKU, $productSkus, 'in');
$result = $productRepository->getList($searchCriteriaBuilder->create());
foreach ($result->getItems() as $product) {
    $productRepository->delete($product);
}

Resolver::getInstance()->requireDataFixture('Magento/ConfigurableProduct/_files/configurable_attribute_rollback.php');

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
