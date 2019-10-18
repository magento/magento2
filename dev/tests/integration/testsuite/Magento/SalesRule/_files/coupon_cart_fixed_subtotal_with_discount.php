<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

include __DIR__ . '/coupon_cart_fixed_discount.php';

use Magento\SalesRule\Model\ResourceModel\Rule as ResourceModel;
use Magento\SalesRule\Model\Rule\Condition\Address;
use Magento\SalesRule\Model\Rule\Condition\Combine;

$salesRule->getConditions()->loadArray(
    [
        'type' => Combine::class,
        'attribute' => null,
        'operator' => null,
        'value' => '1',
        'is_value_processed' => null,
        'aggregator' => 'any',
        'conditions' =>
            [
                [
                    'type' => Address::class,
                    'attribute' => 'base_subtotal_with_discount',
                    'operator' => '>=',
                    'value' => 9,
                    'is_value_processed' => false
                ],
            ],
    ]
);
$salesRule->setDiscountAmount(5);
$objectManager->get(ResourceModel::class)->save($salesRule);
