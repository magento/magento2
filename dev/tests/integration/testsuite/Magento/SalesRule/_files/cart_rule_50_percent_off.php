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

/** @var \Magento\SalesRule\Model\Rule $salesRule */
$salesRule = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\SalesRule\Model\Rule');
$salesRule->setData(
    array(
        'name' => '50% Off on Large Orders',
        'is_active' => 1,
        'customer_group_ids' => array(\Magento\Customer\Service\V1\CustomerGroupServiceInterface::NOT_LOGGED_IN_ID),
        'coupon_type' => \Magento\SalesRule\Model\Rule::COUPON_TYPE_NO_COUPON,
        'conditions' => array(
            array(
                'type' => 'Magento\SalesRule\Model\Rule\Condition\Address',
                'attribute' => 'base_subtotal',
                'operator' => '>',
                'value' => 1000
            )
        ),
        'simple_action' => 'by_percent',
        'discount_amount' => 50,
        'stop_rules_processing' => 1,
        'website_ids' => array(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                'Magento\Framework\StoreManagerInterface'
            )->getWebsite()->getId()
        )
    )
);
$salesRule->save();
