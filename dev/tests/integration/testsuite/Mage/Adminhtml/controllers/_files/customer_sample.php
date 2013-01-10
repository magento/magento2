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
 * @category    Magento
 * @package     Mage_Adminhtml
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/** @var Mage_Customer_Model_Customer $customer */
$customer = Mage::getModel('Mage_Customer_Model_Customer');

$customerData = array(
    'group_id' => 1,
    'website_id' => 1,
    'firstname' => 'test firstname',
    'lastname' => 'test lastname',
    'email' => 'exmaple@domain.com',
    'default_billing' => '_item1',
    'password' => '123123q'
 );
$customer->setData($customerData);
$customer->setId(1);

/** @var Mage_Customer_Model_Address $addressOne  */
$addressOne = Mage::getModel('Mage_Customer_Model_Address');
$addressOneData = array(
    'firstname' => 'test firstname',
    'lastname' => 'test lastname',
    'street' => array('test street'),
    'city' => 'test city',
    'country_id' => 'US',
    'postcode' => '01001',
    'telephone' => '+7000000001',
    'entity_id' => 1
);
$addressOne->setData($addressOneData);
$customer->addAddress($addressOne);

/** @var Mage_Customer_Model_Address $addressTwo  */
$addressTwo = Mage::getModel('Mage_Customer_Model_Address');
$addressTwoData = array(
    'firstname' => 'test firstname',
    'lastname' => 'test lastname',
    'street' => array('test street'),
    'city' => 'test city',
    'country_id' => 'US',
    'postcode' => '01001',
    'telephone' => '+7000000001',
    'entity_id' => 2
);
$addressTwo->setData($addressTwoData);
$customer->addAddress($addressTwo);

/** @var Mage_Customer_Model_Address $addressThree  */
$addressThree = Mage::getModel('Mage_Customer_Model_Address');
$addressThreeData = array(
    'firstname' => 'removed firstname',
    'lastname' => 'removed lastname',
    'street' => array('removed street'),
    'city' => 'removed city',
    'country_id' => 'US',
    'postcode' => '01001',
    'telephone' => '+7000000001',
    'entity_id' => 3
);
$addressThree->setData($addressThreeData);
$customer->addAddress($addressThree);

$customer->save();
