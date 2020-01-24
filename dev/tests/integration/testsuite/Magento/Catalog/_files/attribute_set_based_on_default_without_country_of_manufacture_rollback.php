<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Eav\Model\AttributeSetSearchResults;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Data\Collection;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Model\Entity\Type;

$objectManager = Bootstrap::getObjectManager();

/** @var AttributeSetRepositoryInterface $attributeSetRepository */
$attributeSetRepository = $objectManager->create(AttributeSetRepositoryInterface::class);
/** @var Type $entityType */
$entityType = $objectManager->create(Type::class)
    ->loadByCode(Magento\Catalog\Model\Product::ENTITY);
$sortOrderBuilder = $objectManager->create(SortOrderBuilder::class);
/** @var SearchCriteriaBuilder $searchCriteriaBuilder */
$searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
$searchCriteriaBuilder->addFilter('attribute_set_name', 'custom_attribute_set_wout_com');
$searchCriteriaBuilder->addFilter('entity_type_id', $entityType->getId());
$attributeSetIdSortOrder = $sortOrderBuilder
    ->setField('attribute_set_id')
    ->setDirection(Collection::SORT_ORDER_DESC)
    ->create();
$searchCriteriaBuilder->addSortOrder($attributeSetIdSortOrder);
$searchCriteriaBuilder->setPageSize(1);
$searchCriteriaBuilder->setCurrentPage(1);

/** @var AttributeSetSearchResults $searchResult */
$searchResult = $attributeSetRepository->getList($searchCriteriaBuilder->create());
$items = $searchResult->getItems();

$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

try {
    if (count($items) > 0) {
        /** @var Set $attributeSet */
        $attributeSet = reset($items);
        $attributeSetRepository->deleteById($attributeSet->getId());
    }
} catch (\Exception $e) {
    // In case of test run with DB isolation there is already no object in database
    // since rollback fixtures called after transaction rollback.
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
