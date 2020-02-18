<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Bundle\Model\Product\Price;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogRule\Api\CatalogRuleRepositoryInterface;
use Magento\CatalogRule\Api\Data\RuleInterface;
use Magento\CatalogRule\Api\Data\RuleInterfaceFactory;
use Magento\CatalogRule\Model\Indexer\IndexBuilder;
use Magento\CatalogRule\Model\Rule\Condition\Combine;
use Magento\CatalogRule\Model\Rule\Condition\Product;
use Magento\Customer\Model\Group;
use Magento\TestFramework\Bundle\Model\PrepareBundleLinks;

require __DIR__ . '/../../../Magento/Catalog/_files/category_with_different_price_products.php';

/** @var PrepareBundleLinks $prepareBundleLinks */
$prepareBundleLinks = $objectManager->get(PrepareBundleLinks::class);
/** @var RuleInterfaceFactory $catalogRuleFactory */
$catalogRuleFactory = $objectManager->get(RuleInterfaceFactory::class);
/** @var CatalogRuleRepositoryInterface $catalogRuleRepository */
$catalogRuleRepository = $objectManager->get(CatalogRuleRepositoryInterface::class);
/** @var IndexBuilder $indexBuilder */
$indexBuilder = $objectManager->get(IndexBuilder::class);
$defaultWebsiteId = $storeManager->getWebsite('base')->getId();

$category = $categoryFactory->create();
$category->isObjectNew(true);
$category->setName('Category with bundle product and rule')
    ->setParentId($categoryHelper->getId())
    ->setIsActive(true)
    ->setPosition(1);
$category = $categoryRepository->save($category);

$bundleProduct = $productFactory->create();
$bundleProduct->setTypeId(Type::TYPE_BUNDLE)
    ->setAttributeSetId($bundleProduct->getDefaultAttributeSetId())
    ->setWebsiteIds([$defaultWebsiteId])
    ->setName('Bundle Product')
    ->setSku('dynamic_bundle_product_with_catalog_rule')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(
        [
            'use_config_manage_stock' => 1,
            'qty' => 100,
            'is_qty_decimal' => 0,
            'is_in_stock' => 1,
        ]
    )
    ->setSkuType(0)
    ->setPriceView(0)
    ->setPriceType(Price::PRICE_TYPE_DYNAMIC)
    ->setPrice(null)
    ->setWeightType(0)
    ->setCategoryIds([$category->getId()])
    ->setShipmentType(AbstractType::SHIPMENT_TOGETHER);

$bundleOptionsData = [
    [
        'title' => 'Option 1',
        'default_title' => 'Option 1',
        'type' => 'select',
        'required' => 1,
    ],
];
$bundleSelectionsData = [
    [
        [
            'sku' => $product->getSku(),
            'selection_qty' => 1,
            'selection_price_value' => 0,
            'selection_can_change_qty' => 1,
        ],
        [
            'sku' => $product2->getSku(),
            'selection_qty' => 1,
            'selection_price_value' => 0,
            'selection_can_change_qty' => 1,
        ],
    ]
];
$bundleProduct = $prepareBundleLinks->execute($bundleProduct, $bundleOptionsData, $bundleSelectionsData);
$productRepository->save($bundleProduct);

$ruleData = [
    RuleInterface::NAME => 'Rule for bundle product',
    RuleInterface::IS_ACTIVE => 1,
    'website_ids' => [$defaultWebsiteId],
    'customer_group_ids' => Group::NOT_LOGGED_IN_ID,
    RuleInterface::DISCOUNT_AMOUNT => 50,
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
