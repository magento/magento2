<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Customer\Model\GroupManagement as CustomerGroupManagement;
use Magento\Framework\Api\DataObjectHelper;
use Magento\SalesRule\Api\Data\RuleInterface;
use Magento\SalesRule\Api\Data\RuleLabelInterface;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\SalesRule\Model\Data\Rule as RuleData;
use Magento\SalesRule\Model\Data\RuleLabelFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var RuleRepositoryInterface $ruleRepository */
$ruleRepository = $objectManager->get(RuleRepositoryInterface::class);
/** @var DataObjectHelper $dataObjectHelper */
$dataObjectHelper = Bootstrap::getObjectManager()->get(DataObjectHelper::class);
$ruleLabel = $objectManager->create(RuleLabelInterface::class);
$ruleLabelFactory = $objectManager->get(RuleLabelFactory::class);


/** @var RuleData $salesRule */
$salesRule = $objectManager->create(RuleData::class);
/** @var RuleLabelInterface $ruleLabel */
$ruleLabel = $ruleLabelFactory->create();
$ruleLabel->setStoreId(0);
$ruleLabel->setStoreLabel('50% Off for all orders');
$ruleData = [
        'name' => '50% Off for all orders',
        'is_active' => 1,
        'customer_group_ids' => [CustomerGroupManagement::NOT_LOGGED_IN_ID, 1],
        'coupon_type' => RuleData::COUPON_TYPE_NO_COUPON,
        'conditions' => [],
        'simple_action' => 'by_percent',
        'discount_amount' => 50,
        'discount_step' => 0,
        'website_ids' => [
            $objectManager->get(
                StoreManagerInterface::class
            )->getWebsite()->getId(),
        ],
        'discount_qty' => 0,
        'apply_to_shipping' => 1,
        'simple_free_shipping' => 1,
        'stop_rules_processing' => 0
];
$dataObjectHelper->populateWithArray($salesRule, $ruleData, RuleInterface::class);
$salesRule->setStoreLabels([$ruleLabel]);

$ruleRepository->save($salesRule);
