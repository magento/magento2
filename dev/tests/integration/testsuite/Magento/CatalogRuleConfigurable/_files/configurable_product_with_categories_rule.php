<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogRule\Api\CatalogRuleRepositoryInterface;
use Magento\CatalogRule\Model\Rule;
use Magento\CatalogRule\Model\Rule\Condition\Combine;
use Magento\CatalogRule\Model\Rule\Condition\Product;
use Magento\Customer\Model\Group;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()
    ->requireDataFixture('Magento/ConfigurableProduct/_files/product_configurable_12345.php');

$objectManager = Bootstrap::getObjectManager();

$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$confProduct = $productRepository->get('12345');
$childProduct = $productRepository->get('simple_30');
$categoryLinkManagement = $objectManager->get(CategoryLinkManagementInterface::class);
$categoryLinkManagement->assignProductToCategories($confProduct->getSku(), [3]);
$categoryLinkManagement->assignProductToCategories($childProduct->getSku(), [3, 6]);

$websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
$website = $websiteRepository->get('base');

$rule = $objectManager->create(Rule::class);
$rule->loadPost(
    [
        'name' => 'Categories rule for configurable product',
        'is_active' => '1',
        'stop_rules_processing' => 0,
        'website_ids' => [$website->getId()],
        'customer_group_ids' => Group::NOT_LOGGED_IN_ID,
        'discount_amount' => 50,
        'simple_action' => 'by_percent',
        'from_date' => '',
        'to_date' => '',
        'sort_order' => 0,
        'sub_is_enable' => 0,
        'sub_discount_amount' => 0,
        'conditions' => [
            '1' => [
                'type' => Combine::class,
                'aggregator' => 'all',
                'value' => '1',
                'new_child' => ''
            ],
            '1--1' => [
                'type' => Product::class,
                'attribute' => 'category_ids',
                'operator' => '()',
                'value' => '3'
            ],
            '1--2' => [
                'type' => Product::class,
                'attribute' => 'category_ids',
                'operator' => '!()',
                'value' => '6'
            ],
        ],
    ]
);
$ruleRepository = $objectManager->get(CatalogRuleRepositoryInterface::class);
$ruleRepository->save($rule);
