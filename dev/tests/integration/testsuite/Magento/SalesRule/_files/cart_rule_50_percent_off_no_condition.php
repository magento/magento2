<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/** @var \Magento\Framework\Registry $registry */
/** @var \Magento\SalesRule\Model\Rule $salesRule */
/** @var \Magento\SalesRule\Model\RuleRepository $salesRuleRepository */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$registry = $objectManager->get(\Magento\Framework\Registry::class);
$salesRule = $objectManager->create(\Magento\SalesRule\Model\Rule::class);
$salesRuleRepository = $objectManager->create(\Magento\SalesRule\Model\RuleRepository::class);
$allRules = $salesRuleRepository->getList($objectManager->get(\Magento\Framework\Api\SearchCriteriaInterface::class));
foreach ($allRules->getItems() as $rule) {
    $salesRuleRepository->deleteById($rule->getRuleId());
}
$salesRule->setData(
    [
        'name' => '50% off - July 4',
        'is_active' => 1,
        'customer_group_ids' => [\Magento\Customer\Model\GroupManagement::NOT_LOGGED_IN_ID],
        'coupon_type' => \Magento\SalesRule\Model\Rule::COUPON_TYPE_NO_COUPON,
        'simple_action' => 'by_percent',
        'discount_amount' => 50,
        'discount_step' => 0,
        'stop_rules_processing' => 0,
        'website_ids' => [
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                \Magento\Store\Model\StoreManagerInterface::class
            )->getWebsite()->getId()
        ]
    ]
);
$salesRule->save();

$registry->unregister('Magento/SalesRule/_files/cart_rule_50_percent_off_no_condition/salesRuleId');
$registry->register('Magento/SalesRule/_files/cart_rule_50_percent_off_no_condition/salesRuleId', $salesRule->getId());
