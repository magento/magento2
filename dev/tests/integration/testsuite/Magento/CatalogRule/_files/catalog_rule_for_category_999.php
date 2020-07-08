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
use Magento\CatalogRule\Model\Rule\Condition\Combine;
use Magento\CatalogRule\Model\Rule\Condition\Product;
use Magento\Customer\Model\Group;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Catalog\Model\GetCategoryByName;

$objectManager = Bootstrap::getObjectManager();
/** @var WebsiteRepositoryInterface $websiteRepository */
$websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
$defaultWebsiteId = $websiteRepository->get('base')->getId();
/** @var IndexBuilder $indexBuilder */
$indexBuilder = $objectManager->get(IndexBuilder::class);
/** @var CatalogRuleRepositoryInterface $catalogRuleRepository */
$catalogRuleRepository = $objectManager->get(CatalogRuleRepositoryInterface::class);
/** @var RuleInterfaceFactory $ruleFactory */
$catalogRuleFactory = $objectManager->get(RuleInterfaceFactory::class);
/** @var GetCategoryByName $getCategoryByName */
$getCategoryByName = $objectManager->get(GetCategoryByName::class);

$category = $getCategoryByName->execute('Category 999');
if ($category->getId()) {
    $ruleData = [
        RuleInterface::NAME => 'Catalog rule for category 999',
        RuleInterface::IS_ACTIVE => 1,
        'website_ids' => [$defaultWebsiteId],
        'customer_group_ids' => Group::NOT_LOGGED_IN_ID,
        RuleInterface::DISCOUNT_AMOUNT => 25,
        RuleInterface::SIMPLE_ACTION => 'by_percent',
        'conditions' => [
            '1' => [
                'type' => Combine::class,
                'aggregator' => 'all',
                'value' => '1',
            ],
            '1--1' => [
                'type' => Product::class,
                'attribute' => 'category_ids',
                'operator' => '==',
                'value' => $category->getId(),
            ],
        ],
    ];
    $catalogRule = $catalogRuleFactory->create();
    $catalogRule->loadPost($ruleData);
    $catalogRuleRepository->save($catalogRule);
    $indexBuilder->reindexFull();
}
