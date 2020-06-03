<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogRule\Api\CatalogRuleRepositoryInterface;
use Magento\CatalogRule\Model\Indexer\IndexBuilder;
use Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\TestFramework\Catalog\Model\GetCategoryByName;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture(
    'Magento/Catalog/_files/category_with_different_price_products_rollback.php'
);

$objectManager = Bootstrap::getObjectManager();
/** @var CategoryRepositoryInterface $categoryRepository */
$categoryRepository = $objectManager->get(CategoryRepositoryInterface::class);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);
/** @var GetCategoryByName $getCategoryByName */
$getCategoryByName = $objectManager->create(GetCategoryByName::class);
/** @var  CollectionFactory $ruleCollectionFactory */
$ruleCollectionFactory = $objectManager->get(CollectionFactory::class);
/** @var CatalogRuleRepositoryInterface $ruleRepository */
$ruleRepository = $objectManager->get(CatalogRuleRepositoryInterface::class);
/** @var IndexBuilder $indexBuilder */
$indexBuilder = $objectManager->get(IndexBuilder::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

try {
    $product = $productRepository->get('dynamic_bundle_product_with_catalog_rule', false, null, true);
    $productRepository->delete($product);
} catch (NoSuchEntityException $e) {
    //product already deleted.
}

$category = $getCategoryByName->execute('Category with bundle product and rule');

try {
    $categoryRepository->delete($category);
} catch (NoSuchEntityException $e) {
    //category already deleted.
}

$ruleCollection = $ruleCollectionFactory->create();
$ruleCollection->addFieldToFilter('name', 'Rule for bundle product');
$ruleCollection->setPageSize(1);
$catalogRule = $ruleCollection->getFirstItem();

try {
    $ruleRepository->delete($catalogRule);
} catch (Exception $ex) {
    //Nothing to remove
}

$indexBuilder->reindexFull();

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
