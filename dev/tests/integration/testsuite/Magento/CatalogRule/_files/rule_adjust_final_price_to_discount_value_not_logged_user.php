<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\CatalogRule\Api\CatalogRuleRepositoryInterface;
use Magento\CatalogRule\Api\Data\RuleInterface;
use Magento\CatalogRule\Api\Data\RuleInterfaceFactory;
use Magento\CatalogRule\Model\Indexer\IndexBuilder;
use Magento\Customer\Model\Group;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var IndexBuilder $indexBuilder */
$indexBuilder = $objectManager->get(IndexBuilder::class);
/** @var CatalogRuleRepositoryInterface $catalogRuleRepository */
$catalogRuleRepository = $objectManager->get(CatalogRuleRepositoryInterface::class);
/** @var RuleInterfaceFactory $catalogRuleFactory */
$catalogRuleFactory = $objectManager->get(RuleInterfaceFactory::class);
$catalogRule = $catalogRuleFactory->create(
    [
        'data' => [
            RuleInterface::IS_ACTIVE => 1,
            RuleInterface::NAME => 'Test Catalog Rule for not logged user',
            'customer_group_ids' => Group::NOT_LOGGED_IN_ID,
            RuleInterface::DISCOUNT_AMOUNT => 10,
            'website_ids' => [1],
            RuleInterface::SIMPLE_ACTION => 'to_fixed',
            RuleInterface::STOP_RULES_PROCESSING => false,
            RuleInterface::SORT_ORDER => 0,
            'sub_is_enable' => 0,
            'sub_discount_amount' => 0,
        ]
    ]
);
$catalogRuleRepository->save($catalogRule);
$indexBuilder->reindexFull();
