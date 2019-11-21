<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Customer\Model\GroupManagement;
use Magento\SalesRule\Api\CouponRepositoryInterface;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\Rule;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var Rule $salesRule */
$salesRule = $objectManager->create(Rule::class);
$salesRule->setData(
    [
        'name' => '15$ fixed discount on whole cart',
        'is_active' => 1,
        'customer_group_ids' => [GroupManagement::NOT_LOGGED_IN_ID],
        'coupon_type' => Rule::COUPON_TYPE_SPECIFIC,
        'conditions' => [
            [
                'type' => \Magento\SalesRule\Model\Rule\Condition\Address::class,
                'attribute' => 'base_subtotal',
                'operator' => '>',
                'value' => 45,
            ],
        ],
        'simple_action' => Rule::CART_FIXED_ACTION,
        'discount_amount' => 15,
        'discount_step' => 0,
        'stop_rules_processing' => 1,
        'website_ids' => [
            $objectManager->get(StoreManagerInterface::class)->getWebsite()->getId(),
        ],
        'store_labels' => [

            'store_id' => 0,
            'store_label' => 'TestRule_Coupon',
        ]
    ]
);
$objectManager->get(\Magento\SalesRule\Model\ResourceModel\Rule::class)->save($salesRule);

// Create coupon and assign "15$ fixed discount" rule to this coupon.
$coupon = $objectManager->create(Coupon::class);
$coupon->setRuleId($salesRule->getId())
    ->setCode('CART_FIXED_DISCOUNT_15')
    ->setType(0);
$objectManager->get(CouponRepositoryInterface::class)->save($coupon);
