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
use Magento\Store\Model\WebsiteRepository;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var WebsiteRepository $websiteRepository */
$websiteRepository = $objectManager->get(WebsiteRepository::class);
$baseWebsite = $websiteRepository->get('base');
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
            RuleInterface::NAME => 'Rule adjust final price to discount value. Not logged user.',
            'customer_group_ids' => Group::NOT_LOGGED_IN_ID,
            RuleInterface::DISCOUNT_AMOUNT => 10,
            'website_ids' => [$baseWebsite->getId()],
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
