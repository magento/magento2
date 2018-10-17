<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\SalesRule\Api\CouponRepositoryInterface;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\Rule;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var Rule $salesRule */
$salesRule = getSalesRule('5$ fixed discount on whole cart');
if ($salesRule !== null) {
    /** @var RuleRepositoryInterface $ruleRepository */
    $ruleRepository = $objectManager->get(RuleRepositoryInterface::class);
    $ruleRepository->deleteById($salesRule->getRuleId());
}

$coupon = $objectManager->create(Coupon::class);
$coupon->loadByCode('2?ds5!2d');
if ($coupon->getCouponId()) {
    /** @var CouponRepositoryInterface $couponRepository */
    $couponRepository = $objectManager->get(CouponRepositoryInterface::class);
    $couponRepository->deleteById($coupon->getCouponId());
}

function getSalesRule(string $name)
{
    /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
    $searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
    $searchCriteria = $searchCriteriaBuilder->addFilter('name', $name)
        ->create();

    /** @var RuleRepositoryInterface $ruleRepository */
    $ruleRepository = Bootstrap::getObjectManager()->get(RuleRepositoryInterface::class);
    $items = $ruleRepository->getList($searchCriteria)
        ->getItems();

    return array_pop($items);
}
