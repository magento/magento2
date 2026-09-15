<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

use Magento\CatalogRule\Api\CatalogRuleRepositoryInterface;
use Magento\CatalogRule\Model\Rule;
use Magento\CatalogRule\Model\Rule\Condition\Combine;
use Magento\CatalogRule\Model\Rule\Condition\Product;
use Magento\CatalogRule\Model\RuleFactory;
use Magento\Customer\Model\Group;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture(
    'Magento/CatalogRuleConfigurable/_files/configurable_product_with_percent_rule.php'
);
Resolver::getInstance()->requireDataFixture(
    'Magento/Customer/_files/customer_group.php'
);

$objectManager = Bootstrap::getObjectManager();
/** @var WebsiteRepositoryInterface $websiteRepository */
$websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
/** @var CatalogRuleRepositoryInterface $ruleRepository */
$ruleRepository = $objectManager->get(CatalogRuleRepositoryInterface::class);
/** @var RuleFactory $ruleFactory */
$ruleFactory = $objectManager->get(RuleFactory::class);
/** @var Group $customGroup */
$customGroup = $objectManager->create(Group::class)->load('custom_group', 'customer_group_code');

$notLoggedInRule = $ruleFactory->create();
$notLoggedInRule->loadPost(
    [
        'name' => 'Same priority rule for NOT LOGGED IN customer group',
        'is_active' => '1',
        'stop_rules_processing' => 0,
        'website_ids' => [$websiteRepository->get('base')->getId()],
        'customer_group_ids' => Group::NOT_LOGGED_IN_ID,
        'discount_amount' => 10,
        'simple_action' => 'by_percent',
        'from_date' => '',
        'to_date' => '',
        'sort_order' => 0,
        'sub_is_enable' => 0,
        'sub_discount_amount' => 0,
        'conditions' => [
            '1' => ['type' => Combine::class, 'aggregator' => 'all', 'value' => '1', 'new_child' => ''],
            '1--1' => [
                'type' => Product::class,
                'attribute' => 'sku',
                'operator' => '()',
                'value' => 'simple_10,simple_20'
            ],
        ],
    ]
);
$ruleRepository->save($notLoggedInRule);

$customGroupRule = $ruleFactory->create();
$customGroupRule->loadPost(
    [
        'name' => 'Same priority rule for custom customer group',
        'is_active' => '1',
        'stop_rules_processing' => 0,
        'website_ids' => [$websiteRepository->get('base')->getId()],
        'customer_group_ids' => $customGroup->getId(),
        'discount_amount' => 20,
        'simple_action' => 'by_percent',
        'from_date' => '',
        'to_date' => '',
        'sort_order' => 0,
        'sub_is_enable' => 0,
        'sub_discount_amount' => 0,
        'conditions' => [
            '1' => ['type' => Combine::class, 'aggregator' => 'all', 'value' => '1', 'new_child' => ''],
            '1--1' => [
                'type' => Product::class,
                'attribute' => 'sku',
                'operator' => '()',
                'value' => 'simple_10,simple_20'
            ],
        ],
    ]
);
$ruleRepository->save($customGroupRule);
