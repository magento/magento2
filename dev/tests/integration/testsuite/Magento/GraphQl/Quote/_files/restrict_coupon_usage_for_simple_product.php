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
$ruleId = $coupon->getRuleId();
$salesRule = $ruleRepository->getById($ruleId);

/** @var ConditionInterface $conditionProductSku */
$conditionProductSku = $conditionFactory->create();
$conditionProductSku->setConditionType(\Magento\SalesRule\Model\Rule\Condition\Product::class);
$conditionProductSku->setAttributeName('sku');
$conditionProductSku->setValue('1');
$conditionProductSku->setOperator('!=');
$conditionProductSku->setValue($sku);

/** @var ConditionInterface $conditionProductFound */
$conditionProductFound = $conditionFactory->create();
$conditionProductFound->setConditionType(\Magento\SalesRule\Model\Rule\Condition\Product\Found::class);
$conditionProductFound->setValue('1');
$conditionProductFound->setAggregatorType('all');
$conditionProductFound->setConditions([$conditionProductSku]);

/** @var ConditionInterface $conditionCombine */
$conditionCombine = $conditionFactory->create();
$conditionCombine->setConditionType(\Magento\SalesRule\Model\Rule\Condition\Combine::class);
$conditionCombine->setValue('1');
$conditionCombine->setAggregatorType('all');
$conditionCombine->setConditions([$conditionProductFound]);

$salesRule->setCondition($conditionCombine);
$ruleRepository->save($salesRule);
