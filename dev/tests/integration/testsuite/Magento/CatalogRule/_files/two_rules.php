<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

use Magento\TestFramework\Helper\Bootstrap;

/** @var \Magento\CatalogRule\Model\Rule $rule */
$rule = Bootstrap::getObjectManager()->get('Magento\CatalogRule\Model\RuleFactory')->create();
$rule->loadPost([
    'name' => 'test_rule_one',
    'is_active' => '1',
    'stop_rules_processing' => 0,
    'website_ids' => [1],
    'customer_group_ids' => [0, 1],
    'discount_amount' => 5,
    'simple_action' => 'by_percent',
    'from_date' => '',
    'to_date' => '',
    'conditions' => [
        '1' => [
            'type' => 'Magento\CatalogRule\Model\Rule\Condition\Combine',
            'aggregator' => 'all',
            'value' => '1',
            'new_child' => '',
            'attribute' => null,
            'operator' => null,
            'is_value_processed' => null,
        ],
    ],
]);
$rule->save();
$rule = Bootstrap::getObjectManager()->get('Magento\CatalogRule\Model\RuleFactory')->create();
$rule->loadPost([
        'name' => 'test_rule_two',
        'is_active' => '1',
        'stop_rules_processing' => 0,
        'website_ids' => [1],
        'customer_group_ids' => [0, 1],
        'discount_amount' => 2,
        'simple_action' => 'by_fixed',
        'from_date' => '',
        'to_date' => '',
        'conditions' => [
            '1' => [
                'type' => 'Magento\CatalogRule\Model\Rule\Condition\Combine',
                'aggregator' => 'all',
                'value' => '1',
                'new_child' => '',
                'attribute' => null,
                'operator' => null,
                'is_value_processed' => null,
            ],
        ],
    ]);
$rule->save();
