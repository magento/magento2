<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\Eav\Api\AttributeRepositoryInterface $repository */
$repository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Eav\Api\AttributeRepositoryInterface::class);

/** @var \Magento\Eav\Api\Data\AttributeInterface $skuAttribute */
$skuAttribute = $repository->get(
    'catalog_product',
    'sku'
);
$data = $skuAttribute->getData();
$data['is_used_for_promo_rules'] = 1;
$skuAttribute->setData($data);
$skuAttribute->save();

/** @var \Magento\SalesRule\Model\Rule $rule */
$salesRule = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\SalesRule\Model\Rule::class);
$salesRule->setData(
    [
        'name' => '20% Off',
        'is_active' => 1,
        'customer_group_ids' => [\Magento\Customer\Model\GroupManagement::NOT_LOGGED_IN_ID],
        'coupon_type' => \Magento\SalesRule\Model\Rule::COUPON_TYPE_NO_COUPON,
        'simple_action' => 'by_percent',
        'discount_amount' => 20,
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
    'conditions' =>
        [
                [
                    'type' => \Magento\SalesRule\Model\Rule\Condition\Product\Found::class,
                    'attribute' => null,
                    'operator' => null,
                    'value' => '1',
                    'is_value_processed' => null,
                    'aggregator' => 'all',
                    'conditions' =>
                        [
                                [
                                    'type' => \Magento\SalesRule\Model\Rule\Condition\Product::class,
                                    'attribute' => 'sku',
                                    'operator' => '!=',
                                    'value' => 'product-bundle',
                                    'is_value_processed' => false,
                                ],
                        ],
                ],
        ],
]);

$salesRule->save();

/** @var Magento\Framework\Registry $registry */
$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);

$registry->unregister('_fixture/Magento_SalesRule_Sku_Exclude');
$registry->register('_fixture/Magento_SalesRule_Sku_Exclude', $salesRule);
