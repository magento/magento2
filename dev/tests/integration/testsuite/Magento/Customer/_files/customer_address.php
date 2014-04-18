<?php
/**
 * Customer address fixture with entity_id = 1
 *
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
/** @var \Magento\Customer\Model\Address $customerAddress */
$customerAddress = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Customer\Model\Address'
);
$customerAddress->isObjectNew(true);
$customerAddress->setData(
    array(
        'entity_id' => 1,
        'attribute_set_id' => 2,
        'telephone' => 3468676,
        'postcode' => 75477,
        'country_id' => 'US',
        'city' => 'CityM',
        'company' => 'CompanyName',
        'street' => array('Green str, 67'),
        'lastname' => 'Smith',
        'firstname' => 'John',
        'parent_id' => 1,
        'region_id' => 1
    )
)->setCustomerId(
    1
);
$customerAddress->save();

/** @var \Magento\Customer\Model\Customer $customer */
$customer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Customer\Model\Customer');
$customer->load(1)->save();
