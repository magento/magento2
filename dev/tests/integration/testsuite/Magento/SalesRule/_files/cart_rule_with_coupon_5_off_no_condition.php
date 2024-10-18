<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Customer\Model\GroupManagement;
use Magento\Customer\Model\ResourceModel\Group\Collection;
use Magento\SalesRule\Api\CouponRepositoryInterface;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\Rule;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var Collection $groupCollection */
$groupCollection = $objectManager->get(Collection::class);
$customerGroupIds = $groupCollection->getAllIds();
/** @var Rule $salesRule */
$salesRule = $objectManager->create(Rule::class);
$salesRule->setData(
    [
        'name' => 'cart_rule_with_coupon_5_off_no_condition',
        'is_active' => 1,
        'customer_group_ids' => $groupCollection->getAllIds(),
        'coupon_type' => Rule::COUPON_TYPE_SPECIFIC,
        'simple_action' => Rule::CART_FIXED_ACTION,
        'discount_amount' => 5,
        'discount_step' => 0,
        'stop_rules_processing' => 1,
        'website_ids' => [
            $objectManager->get(StoreManagerInterface::class)->getWebsite()->getId(),
        ],
        'store_labels' => [

            'store_id' => 0,
            'store_label' => 'cart_rule_with_coupon_5_off_no_condition',
        ]
    ]
);
$objectManager->get(\Magento\SalesRule\Model\ResourceModel\Rule::class)->save($salesRule);

// Create coupon and assign "5$ fixed discount" rule to this coupon.
$coupon = $objectManager->create(Coupon::class);
$coupon->setRuleId($salesRule->getId())
    ->setCode('CART_FIXED_DISCOUNT_5')
    ->setType(0);
$objectManager->get(CouponRepositoryInterface::class)->save($coupon);
