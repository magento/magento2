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
//Create customer
$customer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Customer\Model\Customer');
$customer->setWebsiteId(
    1
)->setEntityId(
    1
)->setEntityTypeId(
    1
)->setAttributeSetId(
    0
)->setEmail(
    'CharlesTAlston@teleworm.us'
)->setPassword(
    'password'
)->setGroupId(
    1
)->setStoreId(
    1
)->setIsActive(
    1
)->setFirstname(
    'Charles'
)->setLastname(
    'Alston'
)->setGender(
    2
);
$customer->isObjectNew(true);

// Create address
$address = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Customer\Model\Address');
//  default_billing and default_shipping information would not be saved, it is needed only for simple check
$address->addData(
    array(
        'firstname' => 'Charles',
        'lastname' => 'Alston',
        'street' => '3781 Neuport Lane',
        'city' => 'Panola',
        'country_id' => 'US',
        'region_id' => '51',
        'postcode' => '30058',
        'telephone' => '770-322-3514',
        'default_billing' => 1,
        'default_shipping' => 1
    )
);

// Assign customer and address
$customer->addAddress($address);
$customer->save();

// Mark last address as default billing and default shipping for current customer
$customer->setDefaultBilling($address->getId());
$customer->setDefaultShipping($address->getId());
$customer->save();

/** @var $objectManager \Magento\TestFramework\ObjectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$objectManager->get('Magento\Framework\Registry')->unregister('_fixture/Magento_ImportExport_Customer');
$objectManager->get('Magento\Framework\Registry')->register('_fixture/Magento_ImportExport_Customer', $customer);
