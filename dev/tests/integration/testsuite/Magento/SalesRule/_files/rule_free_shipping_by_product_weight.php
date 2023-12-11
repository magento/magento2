<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/SalesRule/_files/cart_rule_free_shipping.php');

$objectManager = Bootstrap::getObjectManager();
/** @var Magento\Framework\Registry $registry */
$registry = $objectManager->get(\Magento\Framework\Registry::class);
$salesRule = $registry->registry('cart_rule_free_shipping');
$row =
    [
        'name' => 'Free shipping if item weight <= 1',
        'conditions' => [
            1 =>
                [
                    'type' => \Magento\SalesRule\Model\Rule\Condition\Combine::class,
                    'attribute' => null,
                    'operator' => null,
                    'value' => '1',
                    'is_value_processed' => null,
                    'aggregator' => 'all',
                    'conditions' => [
                        [
                            'type' => Magento\SalesRule\Model\Rule\Condition\Address::class,
                            'attribute' => 'weight',
                            'operator' => '<=',
                            'value' => '1',
                            'is_value_processed' => false,
                        ]
                    ]
                ]

        ],
        'actions' => [],
    ];
$salesRule->loadPost($row);
$salesRule->save();
