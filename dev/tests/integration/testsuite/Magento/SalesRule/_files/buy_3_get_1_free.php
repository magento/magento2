<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Customer\Model\GroupManagement;
use Magento\SalesRule\Model\Rule;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var Rule $salesRule */
$salesRule = $objectManager->create(Rule::class);
$salesRule->setData(
    [
        'name' => 'Buy 3 And Get 1 Free',
        'is_active' => 1,
        'customer_group_ids' => [GroupManagement::NOT_LOGGED_IN_ID],
        'coupon_type' => Rule::COUPON_TYPE_NO_COUPON,
        'conditions' => [],
        'simple_action' => Rule::BUY_X_GET_Y_ACTION,
        'discount_amount' => 1,
        'discount_step' => 3,
        'stop_rules_processing' => 0,
        'store_labels' => [0 => ' Get 1 item free for every 3 you buy'],
        'website_ids' => [
            $objectManager->get(StoreManagerInterface::class)->getWebsite()->getId(),
        ],
    ]
);
$objectManager->get(\Magento\SalesRule\Model\ResourceModel\Rule::class)->save($salesRule);
