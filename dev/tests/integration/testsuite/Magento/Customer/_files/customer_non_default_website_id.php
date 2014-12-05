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

/**
 * @var \Magento\Store\Model\Website $website
 */
$website = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Store\Model\Website');
$website->setName('new Website')->setCode('newwebsite')->save();

$websiteId = $website->getId();


$customer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Customer\Model\Customer');
/** @var Magento\Customer\Model\Customer $customer */
$customer->setWebsiteId(
    $websiteId
)->setId(
        1
    )->setEntityTypeId(
        1
    )->setAttributeSetId(
        1
    )->setEmail(
        'customer2@example.com'
    )->setPassword(
        'password'
    )->setGroupId(
        1
    )->setStoreId(
        $website->getStoreId()
    )->setIsActive(
        1
    )->setFirstname(
        'Firstname'
    )->setLastname(
        'Lastname'
    )->setDefaultBilling(
        1
    )->setDefaultShipping(
        1
    );
$customer->isObjectNew(true);

/** @var \Magento\Customer\Model\Address $addressOne  */
$addressOne = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Customer\Model\Address');
$addressOneData = [
    'firstname' => 'Firstname',
    'lastname' => 'LastName',
    'street' => ['test street'],
    'city' => 'test city',
    'country_id' => 'US',
    'postcode' => '01001',
    'telephone' => '+7000000001',
    'entity_id' => 1
];
$addressOne->setData($addressOneData);
$customer->addAddress($addressOne);

/** @var \Magento\Customer\Model\Address $addressTwo  */
$addressTwo = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Customer\Model\Address');
$addressTwoData = [
    'firstname' => 'test firstname',
    'lastname' => 'test lastname',
    'street' => ['test street'],
    'city' => 'test city',
    'country_id' => 'US',
    'postcode' => '01001',
    'telephone' => '+7000000001',
    'entity_id' => 2
];
$addressTwo->setData($addressTwoData);
$customer->addAddress($addressTwo);

/** @var \Magento\Customer\Model\Address $addressThree  */
$addressThree = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Customer\Model\Address');
$addressThreeData = [
    'firstname' => 'removed firstname',
    'lastname' => 'removed lastname',
    'street' => ['removed street'],
    'city' => 'removed city',
    'country_id' => 'US',
    'postcode' => '01001',
    'telephone' => '+7000000001',
    'entity_id' => 3
];
$addressThree->setData($addressThreeData);
$customer->addAddress($addressThree);
$customer->save();
