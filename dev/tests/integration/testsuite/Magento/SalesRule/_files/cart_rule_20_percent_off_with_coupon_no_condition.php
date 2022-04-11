<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\RuleFactory;
use Magento\SalesRule\Api\CouponRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Customer\Model\GroupManagement;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;

$objectManager = Bootstrap::getObjectManager();
$salesRuleFactory = $objectManager->create(RuleFactory::class);
/** @var Rule $salesRule */
$salesRule = $salesRuleFactory->create();
$salesRule->setData(
    [
        'name' => 'Rule2',
        'is_active' => 1,
        'customer_group_ids' => [GroupManagement::NOT_LOGGED_IN_ID],
        'coupon_type' => Rule::COUPON_TYPE_SPECIFIC,
        'simple_action' => 'by_percent',
        'discount_amount' => 20,
        'discount_step' => 0,
        'stop_rules_processing' => 0,
        'website_ids' => [
            $objectManager->get(
                StoreManagerInterface::class
            )->getWebsite()->getId()
        ]
    ]
);
$salesRule->save();

// Create specific coupon to cover "20% of product price discount" to cart total
$coupon = $objectManager->create(Coupon::class);
$coupon->setRuleId($salesRule->getId())
    ->setCode('123')
    ->setType(0);

/** @var CouponRepositoryInterface $couponRepository */
$couponRepository = $objectManager->get(CouponRepositoryInterface::class);
$couponRepository->save($coupon);

/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);

$registry->unregister('cart_rule_20_percent_off_with_coupon_no_condition');
$registry->register('cart_rule_20_percent_off_with_coupon_no_condition', $salesRule);
