<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Registry;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\SalesRule\Model\Rule;
use Magento\TestFramework\Helper\Bootstrap;

if (!isset($skus)) {
    $skus = [
        'bundle_product_with_dynamic_price',
        'simple1',
        'simple2',
    ];
}
$objectManager = Bootstrap::getObjectManager();

/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var SearchCriteriaBuilder $searchCriteriaBuilder */
$searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
$names = array_map(
    function ($sku) {
        return '50% Off for ' . $sku;
    },
    $skus
);
$searchCriteria = $searchCriteriaBuilder->addFilter('name', $names, 'in')
    ->create();
/** @var RuleRepositoryInterface $ruleRepository */
$ruleRepository = $objectManager->get(RuleRepositoryInterface::class);
$items = $ruleRepository->getList($searchCriteria)
    ->getItems();
/** @var Rule $salesRule */
foreach ($items as $salesRule) {
    if ($salesRule !== null && $salesRule->getRuleId()) {
        $ruleRepository->deleteById($salesRule->getRuleId());
    }
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
