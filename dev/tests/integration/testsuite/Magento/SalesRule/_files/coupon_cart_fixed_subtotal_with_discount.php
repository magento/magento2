<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\SalesRule\Model\ResourceModel\Rule as ResourceModel;
use Magento\SalesRule\Model\Rule\Condition\Address;
use Magento\SalesRule\Model\Rule\Condition\Combine;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory;

Resolver::getInstance()->requireDataFixture('Magento/SalesRule/_files/coupon_cart_fixed_discount.php');

$objectManager = Bootstrap::getObjectManager();
/** @var ResourceModel $ruleResource */
$ruleResource = $objectManager->get(ResourceModel::class);
$salesRule = $objectManager->get(CollectionFactory::class)->create()
    ->addFieldToFilter('name', '15$ fixed discount on whole cart')
    ->getFirstItem();
$salesRule->getConditions()->loadArray(
    [
        'type' => Combine::class,
        'attribute' => null,
        'operator' => null,
        'value' => '1',
        'is_value_processed' => null,
        'aggregator' => 'any',
        'conditions' => [
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
$ruleResource->save($salesRule);
