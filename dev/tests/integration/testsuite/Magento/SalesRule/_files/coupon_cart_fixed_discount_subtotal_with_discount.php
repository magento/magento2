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
$salesRule = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\SalesRule\Model\Rule::class);
$salesRule->setData(
    [
        'name' => '5$ fixed discount on whole cart',
        'is_active' => 1,
        'customer_group_ids' => [GroupManagement::NOT_LOGGED_IN_ID],
        'coupon_type' => Rule::COUPON_TYPE_SPECIFIC,
        'simple_action' => Rule::CART_FIXED_ACTION,
        'discount_amount' => 8,
        'discount_step' => 0,
        'stop_rules_processing' => 0,
        'website_ids' => [
            $objectManager->get(StoreManagerInterface::class)->getWebsite()->getId(),
        ],
    ]
);
$salesRule->getConditions()->loadArray([
    'type' => \Magento\SalesRule\Model\Rule\Condition\Combine::class,
    'attribute' => null,
    'operator' => null,
    'value' => '1',
    'is_value_processed' => null,
    'aggregator' => 'any',
    'conditions' =>
        [
            [
                'type' => \Magento\SalesRule\Model\Rule\Condition\Address::class,
                'attribute' => 'base_subtotal_with_discount',
                'operator' => '>=',
                'value' => 9,
                'is_value_processed' => false
            ],
        ],
]);

$salesRule->save();

// Create coupon and assign "5$ fixed discount" rule to this coupon.
$coupon = $objectManager->create(Coupon::class);
$coupon->setRuleId($salesRule->getId())
    ->setCode('CART_FIXED_DISCOUNT_5')
    ->setType(0);
$objectManager->get(CouponRepositoryInterface::class)->save($coupon);
