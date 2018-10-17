<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\ObjectManagerInterface;
use Magento\SalesRule\Api\Data\RuleInterface;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var ObjectManagerInterface $objectManager */
$objectManager = Bootstrap::getObjectManager();

/** @var \Magento\Framework\Registry $registry */
$registry = $objectManager->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var RuleRepositoryInterface $ruleRepository */
$ruleRepository = $objectManager->get(RuleRepositoryInterface::class);
/** @var SearchCriteriaBuilder $searchCriteriaBuilder */
$searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
$searchCriteria = $searchCriteriaBuilder->addFilter('name', '10$ discount')
    ->create();
$items = $ruleRepository->getList($searchCriteria)
    ->getItems();

/** @var RuleInterface $item */
foreach ($items as $item) {
    $ruleRepository->deleteById($item->getRuleId());
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
