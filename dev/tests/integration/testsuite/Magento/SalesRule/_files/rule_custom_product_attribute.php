<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$entityTypeId = $objectManager->create(\Magento\Eav\Model\Entity\Type::class)
    ->loadByCode('catalog_category')
    ->getId();

$attributeData = [
        'attribute_code' => 'attribute_for_sales_rule_1',
        'entity_type_id' => $entityTypeId,
        'backend_type' => 'varchar',
        'is_required' => 1,
        'is_user_defined' => 1,
        'is_unique' => 0,
        'is_used_for_promo_rules' => 1,
];

/** @var \Magento\Eav\Model\Entity\Attribute $attribute */
$attribute = $objectManager->create(\Magento\Eav\Model\Entity\Attribute::class);
$attribute->setData($attributeData);
$attribute->save();

/** @var \Magento\SalesRule\Model\Rule $rule */
$salesRule = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\SalesRule\Model\Rule::class);
$salesRule->setData(
    [
        'name' => '50% Off on some attribute',
        'is_active' => 1,
        'customer_group_ids' => [\Magento\Customer\Model\GroupManagement::NOT_LOGGED_IN_ID],
        'coupon_type' => \Magento\SalesRule\Model\Rule::COUPON_TYPE_NO_COUPON,
        'simple_action' => 'by_percent',
        'discount_amount' => 50,
        'discount_step' => 0,
        'stop_rules_processing' => 1,
        'website_ids' => [
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                \Magento\Store\Model\StoreManagerInterface::class
            )->getWebsite()->getId()
        ]
    ]
);

$salesRule->getConditions()->loadArray([
    'type' => \Magento\SalesRule\Model\Rule\Condition\Combine::class,
    'attribute' => null,
    'operator' => null,
    'value' => '1',
    'is_value_processed' => null,
    'aggregator' => 'all',
    'conditions' => [
        [
            'type' => \Magento\SalesRule\Model\Rule\Condition\Product\Found::class,
            'attribute' => null,
            'operator' => null,
            'value' => '0',
            'is_value_processed' => null,
            'aggregator' => 'all',
            'conditions' => [
                [
                    'type' => \Magento\SalesRule\Model\Rule\Condition\Product::class,
                    'attribute' => 'attribute_for_sales_rule_1',
                    'operator' => '==',
                    'value' => '2',
                    'is_value_processed' => false,
                ],
            ],
        ],
    ],
]);

$salesRule->save();
