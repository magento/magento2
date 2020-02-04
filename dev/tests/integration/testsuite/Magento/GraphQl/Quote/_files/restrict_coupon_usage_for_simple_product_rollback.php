<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\SalesRule\Api\Data\ConditionInterface;
use Magento\SalesRule\Api\Data\ConditionInterfaceFactory;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\SalesRule\Model\CouponFactory;
use Magento\TestFramework\Helper\Bootstrap;

/** @var CouponFactory $couponFactory */
$couponFactory = Bootstrap::getObjectManager()->get(CouponFactory::class);
/** @var ConditionInterfaceFactory $conditionFactory */
$conditionFactory = Bootstrap::getObjectManager()->get(ConditionInterfaceFactory::class);
/** @var RuleRepositoryInterface $ruleRepository */
$ruleRepository = Bootstrap::getObjectManager()->get(RuleRepositoryInterface::class);

$couponCode = '2?ds5!2d';
$sku = 'simple_product';

$coupon = $couponFactory->create();
$coupon->loadByCode($couponCode);

if ($coupon->getId()) {
    $ruleId = $coupon->getRuleId();
    $salesRule = $ruleRepository->getById($ruleId);

    /** @var ConditionInterface $conditionCombine */
    $conditionCombine = $conditionFactory->create();
    $conditionCombine->setConditions([]);

    $salesRule->setCondition($conditionCombine);
    $ruleRepository->save($salesRule);
}
