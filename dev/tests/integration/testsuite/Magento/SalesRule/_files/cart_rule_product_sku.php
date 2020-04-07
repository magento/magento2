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
use Magento\SalesRule\Model\Rule\Condition\Combine;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

if (!isset($sku)) {
    $sku = 'simple1';
}
$objectManager = Bootstrap::getObjectManager();
/** @var Rule $salesRule */
$salesRule = $objectManager->create(Rule::class);
$salesRule->loadPost(
    [
        'name' => '50% Off for ' . $sku,
        'is_active' => 1,
        'customer_group_ids' => [GroupManagement::NOT_LOGGED_IN_ID],
        'coupon_type' => Rule::COUPON_TYPE_SPECIFIC,
        'simple_action' => 'by_percent',
        'discount_amount' => 50,
        'discount_step' => 0,
        'stop_rules_processing' => 0,
        'website_ids' => [
            $objectManager->get(StoreManagerInterface::class)->getWebsite()->getId()
        ],
        'conditions' => [
            1 => [
                    'type' => Combine::class,
                    'attribute' => null,
                    'operator' => null,
                    'value' => '1',
                    'is_value_processed' => null,
                    'aggregator' => 'all',
            ]
        ],
        'actions' => [
            1 => [
                'type' => Magento\SalesRule\Model\Rule\Condition\Product\Combine::class,
                'attribute' => null,
                'operator' => null,
                'value' => '1',
                'is_value_processed' => null,
                'aggregator' => 'all',
                'actions' => [
                    1 => [
                        'type' => Magento\SalesRule\Model\Rule\Condition\Product::class,
                        'attribute' => 'sku',
                        'operator' => '==',
                        'value' => $sku,
                        'is_value_processed' => false,
                    ]
                ]
            ]
        ],
        'store_labels' => [

                'store_id' => 0,
                'store_label' => 'Promo code for ' . $sku,

        ]
    ]
);
$salesRule->save();
$coupon = $objectManager->create(Coupon::class);
$coupon->setRuleId($salesRule->getId())
    ->setCode($sku . '_coupon_code')
    ->setType(0);
$objectManager->get(CouponRepositoryInterface::class)->save($coupon);
