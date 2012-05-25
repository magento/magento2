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
 * @package     Mage_ImportExport
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$customer = new Mage_Customer_Model_Customer();

$customer->setWebsiteId(1)
    ->setEntityId(1)
    ->setEntityTypeId(1)
    ->setAttributeSetId(0)
    ->setEmail('customer@example.com')
    ->setPassword('password')
    ->setGroupId(1)
    ->setStoreId(1)
    ->setIsActive(1)
    ->setFirstname('Firstname')
    ->setLastname('Lastname')
    ->setDefaultBilling(1)
    ->setDefaultShipping(1);
$customer->isObjectNew(true);
$customer->save();

$customer = new Mage_Customer_Model_Customer();
$customer->setWebsiteId(1)
    ->setEntityId(2)
    ->setEntityTypeId(1)
    ->setAttributeSetId(0)
    ->setEmail('julie.worrell@example.com')
    ->setPassword('password')
    ->setGroupId(1)
    ->setStoreId(1)
    ->setIsActive(1)
    ->setFirstname('Julie')
    ->setLastname('Worrell')
    ->setDefaultBilling(1)
    ->setDefaultShipping(1);
$customer->isObjectNew(true);
$customer->save();

$customer = new Mage_Customer_Model_Customer();
$customer->setWebsiteId(1)
    ->setEntityId(3)
    ->setEntityTypeId(1)
    ->setAttributeSetId(0)
    ->setEmail('david.lamar@example.com')
    ->setPassword('password')
    ->setGroupId(1)
    ->setStoreId(1)
    ->setIsActive(1)
    ->setFirstname('David')
    ->setLastname('Lamar')
    ->setDefaultBilling(1)
    ->setDefaultShipping(1);
$customer->isObjectNew(true);
$customer->save();