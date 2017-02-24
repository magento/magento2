<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require 'cart_rule_free_shipping.php';
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
