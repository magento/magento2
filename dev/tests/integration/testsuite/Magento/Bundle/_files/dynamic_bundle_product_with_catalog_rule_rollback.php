<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\CatalogRule\Api\CatalogRuleRepositoryInterface;
use Magento\CatalogRule\Model\Indexer\IndexBuilder;
use Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Catalog\Model\GetCategoryByName;

require __DIR__ . '/../../../Magento/Catalog/_files/category_with_different_price_products_rollback.php';

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
