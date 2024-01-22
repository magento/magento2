<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

$bootstrap = Bootstrap::getObjectManager();

/** @var Registry $registry */
$registry = $bootstrap->get(Registry::class);

/** @var RuleRepositoryInterface $ruleRepository */
$ruleRepository = $bootstrap->get(RuleRepositoryInterface::class);

$salesRuleName = '40% Off on Large Orders';
$filterGroup = $bootstrap->get(FilterGroup::class);
$filterGroup->setData('name', $salesRuleName);
$searchCriteria = $bootstrap->create(SearchCriteriaInterface::class);
$searchCriteria->setFilterGroups([$filterGroup]);
$items = $ruleRepository->getList($searchCriteria)->getItems();
if ($items) {
    try {
        foreach ($items as $item) {
            $ruleRepository->deleteById($item->getRuleId());
        }
    } catch (NoSuchEntityException $e) {
    }
}
