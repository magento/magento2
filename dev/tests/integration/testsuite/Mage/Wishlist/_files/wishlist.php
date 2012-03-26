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
 * @package     Mage_Wishlist
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require __DIR__ . '/../../Customer/_files/customer.php';
require __DIR__ . '/../../Catalog/_files/product_simple.php';

$wishlist = new Mage_Wishlist_Model_Wishlist;
$wishlist->loadByCustomer($customer->getId(), true);
$item = $wishlist->addNewItem($product, new Varien_Object(array(
//    'product' => '1',
//    'related_product' => '',
//    'options' => array(
//        1 => '1-text',
//        2 => array('month' => 1, 'day' => 1, 'year' => 2001, 'hour' => 1, 'minute' => 1),
//        3 => '1',
//        4 => '1',
//    ),
//    'validate_datetime_2' => '',
//    'qty' => '1',
)));
$wishlist->save();
