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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
if (!Mage::registry('order')) {
    require 'order.php';
}
/** @var $order Mage_Sales_Model_Order */
$order = Mage::registry('order');

$orderService = new Mage_Sales_Model_Service_Order($order);
$invoice = $orderService->prepareInvoice();
$invoice->register();
$invoice->getOrder()->setIsInProcess(true);
$transactionSave = Mage::getModel('Mage_Core_Model_Resource_Transaction');
$transactionSave->addObject($invoice)
    ->addObject($invoice->getOrder())
    ->save();

Mage::register('invoice', $invoice);
$order2 = Mage::registry('order2');
$orderService2 = new Mage_Sales_Model_Service_Order($order2);
$invoice2 = $orderService2->prepareInvoice();
$invoice2->register();
$invoice2->getOrder()->setIsInProcess(true);
$transactionSave2 = Mage::getModel('Mage_Core_Model_Resource_Transaction');
$transactionSave2->addObject($invoice2)
    ->addObject($invoice2->getOrder())
    ->save();

Mage::register('invoice2', $invoice2);
