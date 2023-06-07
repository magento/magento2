<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Api\CouponRepositoryInterface;

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var Magento\Framework\Registry $registry */
$registry = $objectManager->get(\Magento\Framework\Registry::class);

/** @var \Magento\SalesRule\Model\Rule $salesRule */
$salesRule = $objectManager->create(\Magento\SalesRule\Model\Rule::class);
$salesRule->setData(
    [
        'name' => '100% discount on orders for registered customers',
        'is_active' => 1,
        'customer_group_ids' => [1],
        'coupon_type' => Rule::COUPON_TYPE_SPECIFIC,
        'conditions' => [],
        'simple_action' => 'by_percent',
        'discount_amount' => 100,
        'discount_step' => 0,
        'stop_rules_processing' => 1,
        'website_ids' => [
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                \Magento\Store\Model\StoreManagerInterface::class
            )->getWebsite()->getId(),
        ],
        'discount_qty' => 0,
        'apply_to_shipping' => 1,
        'simple_free_shipping' => 1,
    ]
);
$objectManager->get(\Magento\SalesRule\Model\ResourceModel\Rule::class)->save($salesRule);

// Create specific coupon to cover 100% cart total
$coupon = $objectManager->create(Coupon::class);
$coupon->setRuleId($salesRule->getId())
    ->setCode('free_use')
    ->setType(0);

/** @var CouponRepositoryInterface $couponRepository */
$couponRepository = $objectManager->get(CouponRepositoryInterface::class);
$couponRepository->save($coupon);
