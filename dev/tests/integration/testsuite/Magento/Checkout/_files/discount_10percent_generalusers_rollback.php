<?php
/**
 * SalesRule 10% discount coupon
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder */
$criteriaBuilder = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->get('Magento\Framework\Api\SearchCriteriaBuilder');
$criteriaBuilder->addFilter('name', 'Test Coupon for General');

/** @var \Magento\SalesRule\Api\RuleRepositoryInterface $salesRuleRepository */
$salesRuleRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->get('Magento\SalesRule\Api\RuleRepositoryInterface');
$list = $salesRuleRepository->getList($criteriaBuilder->create());
foreach ($list->getItems() as $item) {
    $salesRuleRepository->deleteById($item->getRuleId());
}

