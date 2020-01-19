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
        'name' => '5$ fixed discount on whole cart',
        'is_active' => 1,
        'customer_group_ids' => [GroupManagement::NOT_LOGGED_IN_ID],
        'coupon_type' => Rule::COUPON_TYPE_SPECIFIC,
        'conditions' => [],
        'simple_action' => Rule::CART_FIXED_ACTION,
        'discount_amount' => 5,
        'discount_step' => 0,
        'stop_rules_processing' => 1,
        'store_labels' => [0 => '5$ fixed discount on whole cart'],
        'website_ids' => [
            $objectManager->get(StoreManagerInterface::class)->getWebsite()->getId(),
        ],
    ]
);
$objectManager->get(\Magento\SalesRule\Model\ResourceModel\Rule::class)->save($salesRule);

// Create coupon and assign "15$ fixed discount" rule to this coupon.
$coupon = $objectManager->create(Coupon::class);
$coupon->setRuleId($salesRule->getId())
    ->setCode('2?ds5!2d')
    ->setType(0);

/** @var CouponRepositoryInterface $couponRepository */
$couponRepository = $objectManager->get(CouponRepositoryInterface::class);
$couponRepository->save($coupon);
